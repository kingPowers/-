
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8" />
<title>1122</title>
<script type="text/javascript" src="./jquery.min.js"></script>
<script type="text/javascript" src="./myorder.js"></script>
</head>

<body>
<form action='' method='post' id="picture">
	<img src='./sc.jpg' class='preview'/>
	<input type="file" name='user_info[]' multiple="multiple" accept="image/png,image/jpg,image/jpeg"/>
	<input type="button" name='sub' value='�ύ'/>
	<div style='width:300px;height:60px;border:1px solid red;position:relative;'>
		<div id='process' style='height:100%;display:block;position:relative;background:blue;width:0%;'></div>
	</div>
</form>
<?php 
	var_dump($_REQUEST);
	var_dump($_FILES);
	if(!empty($_FILES)){
		foreach($_FILES['user_info']['tmp_name'] as $k=>$v){
			move_uploaded_file($v,time().".jpg");
		}
		
	}
?>





</body>
<script type="text/javascript">
	var orderPic = new orderPic();
	//ע���ϴ�ͼƬ��form
	var options = {
			'maxNum':3,//�ϴ�ͼƬ�������
			'formId':"picture",//formID 
			'UploadUrl':"./index.php",
			'otherInputParams':<?php echo json_encode(['_cmd_'=>"manager","type"=>"home"]);?>
	};
	orderPic.registerForm(options);
</script>
</html>
