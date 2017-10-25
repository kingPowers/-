<?php 
/* 
* PHP简单利用token防止表单重复提交 
* 还可以在前端提交成功后清空表单reset();
*/ 
session_start(); 
header("Content-Type: text/html;charset=utf-8"); 
function set_token() { 
    $_SESSION['token'] = md5(microtime(true)); 
} 
 
function valid_token() { 
    $return = $_REQUEST['token'] === $_SESSION['token'] ? true : false; 
    set_token(); 
    return $return; 
} 
 
//如果token为空则生成一个token 
if(!isset($_SESSION['token']) || $_SESSION['token']=='') { 
    set_token(); 
} 
 
if(isset($_POST['web'])){ 
    if(!valid_token()){ 
        echo "token error，请不要重复提交！"; 
    }else{ 
        echo '成功提交，Value:'.$_POST['web']; 
    } 
}else{ 
?> 
    <form method="post" action="">   
        <input type="hidden" name="token" value="<?php echo $_SESSION['token']?>">   
        <input type="text" class="input" name="web" value="www.helloweba.com">   
        <input type="submit" class="btn" value="提交" />   
    </form> 
<?php     
} 
?> 