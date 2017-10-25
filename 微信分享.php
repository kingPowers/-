<?php
header("content-type:text/html;charset=utf8");
/*

 *一。登录微信公众平台，点击公众号设置。。。。获取APPID和AppSecret；
 *后端
 *二。获取令牌token:token有时间限制，有效期为2小时，而且从网上获取的次数有限；
 *三。获取ticket:ticket有效期为7200秒；
 *四。签名：将ticket、noncestr、timestamp、分享的url按字母顺序连接起来，进行sha1签名。
            （timestamp = time();当时的时间戳；noncestr是你设置的任意字符串；）
            返回值就是签名；
 *前端JS
 *五。
 *生成签名后，就可以使用js代码了。在你的html中，进行如下设置即可。
 <script type="text/javascript" src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
 <script type="text/javascript">
 // 微信配置
  wx.config({
    debug: false, 
    appId: "你的AppID", 
    timestamp: '上一步生成的时间戳', 
    nonceStr: '上一步中的字符串', 
    signature: '上一步生成的签名',
    jsApiList: ['onMenuShareTimeline', 'onMenuShareAppMessage'] // 功能列表，我们要使用JS-SDK的什么功能
});
  写具体的功能方法
  (微信右上角分享）
  wx.ready(function(){
     // 获取“分享到朋友圈”按钮点击状态及自定义分享内容接口
    wx.onMenuShareTimeline({
        title: '分享标题', // 分享标题
        link:"分享的url,以http或https开头",
        imgUrl: "分享图标的url,以http或https开头" // 分享图标
    });
    
    // 获取“分享给朋友”按钮点击状态及自定义分享内容接口
    wx.onMenuShareAppMessage({
        title: '分享标题', // 分享标题
        desc: "分享描述", // 分享描述
        link:"分享的url,以http或https开头",
        imgUrl: "分享图标的url,以http或https开头", // 分享图标
        type: 'link', // 分享类型,music、video或link，不填默认为link
    });
    ......
  })
  自定义分享：自己定义点击事件等
*/
 // 实例1:
 // 后端：
   function wx_get_token() {//获取token
    $token = S('access_token');
    if (!$token) {//判断token是否失效
        $res = file_get_contents('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.'你的AppID'.'&secret='.'你的AppSecret');
        $res = json_decode($res, true);
        $token = $res['access_token'];
        // 注意：这里需要将获取到的token缓存起来（或写到数据库中）
        // 不能频繁的访问https://api.weixin.qq.com/cgi-bin/token，每日有次数限制
        // 通过此接口返回的token的有效期目前为2小时。令牌失效后，JS-SDK也就不能用了。
        // 因此，这里将token值缓存1小时，比2小时小。缓存失效后，再从接口获取新的token，这样
        // 就可以避免token失效。
        // S()是ThinkPhp的缓存函数，如果使用的是不ThinkPhp框架，可以使用你的缓存函数，或使用数据库来保存。
        S('access_token', $token, 3600);
    }
    return $token;
}
  //接口返回值$res:
  //{"access_token":"ACCESS_TOKEN","expires_in":7200}//  {"access_token":"vdlThyTfyB0N5eMoi3n_aMFMKPuwkE0MgyGf_0h0fpzL8p_hsdUX8VGxz5oSXuq5dM69l
  //xP9wBwN9Yzg-0kVHY33BykRC0YXZZZ-WdxEic4","expires_in":7200}
  
  function wx_get_jsapi_ticket(){//获取ticket
    $ticket = "";
    do{
        $ticket = S('wx_ticket');
        if (!empty($ticket)) {
            break;
        }
        $token = S('access_token');
        if (empty($token)){//判断token值是否失效，失效重新获取
            wx_get_token();
        }
        $token = S('access_token');
        if (empty($token)) {
            logErr("get access token error.");
            break;
        }
        $url2 = sprintf("https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=%s&type=jsapi",
            $token);
        $res = file_get_contents($url2);
        $res = json_decode($res, true);
        $ticket = $res['ticket'];
        // 注意：这里需要将获取到的ticket缓存起来（或写到数据库中）
        // ticket和token一样，不能频繁的访问接口来获取，在每次获取后，我们把它保存起来。
        S('wx_ticket', $ticket, 3600);
    }while(0);
    return $ticket;
}
//接口返回值：
/*{"errcode":0,"errmsg":"ok","ticket":"sM4AOVdWfPE4DxkXGEs8VMKv7FMCPm-I98-klC6SO3Q3AwzxqljYWtzTCxIH9hDOXZCo9cgfHI6kwbe_YWtOQg","expires_in":7200}*/
 
 function signature(){
            $timestamp = time();
            $wxnonceStr = "任意字符串";
            $wxticket = wx_get_jsapi_ticket();
            $wxOri = sprintf("jsapi_ticket=%s&noncestr=%s&timestamp=%s&url=%s",
                $wxticket, $wxnonceStr, $timestamp,
                '要分享的url(从http开始，如果有参数，包含参数）'
                );
           $signature = sha1($wxOri);
 }
?>
<!DOCTYPE html>
<html>
<head>
  <title></title>
  <script type="text/javascript">
      wx.config({
    debug: false, 
    appId: "你的AppID", 
    timestamp: '上一步生成的时间戳', 
    nonceStr: '上一步中的字符串', 
    signature: '上一步生成的签名',
    jsApiList: ['onMenuShareTimeline', 'onMenuShareAppMessage'] // 功能列表，我们要使用JS-SDK的什么功能
     });
  //写具体的功能方法
  wx.ready(function(){
     // 获取“分享到朋友圈”按钮点击状态及自定义分享内容接口
    wx.onMenuShareTimeline({
        title: '分享标题', // 分享标题
        link:"分享的url,以http或https开头",
        imgUrl: "分享图标的url,以http或https开头" // 分享图标
    });
    // 获取“分享给朋友”按钮点击状态及自定义分享内容接口
    wx.onMenuShareAppMessage({
        title: '分享标题', // 分享标题
        desc: "分享描述", // 分享描述
        link:"分享的url,以http或https开头",
        imgUrl: "分享图标的url,以http或https开头", // 分享图标
        type: 'link', // 分享类型,music、video或link，不填默认为link
    });
  })
  </script>
</head>
<body>

</body>
</html>