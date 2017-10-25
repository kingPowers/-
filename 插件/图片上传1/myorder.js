/*
 * 
 * 贷后管家
 * 上传订单图片
 * 
 * 
 */
//document.write("<script type='text/javascript' src='./jquery-1.9.1.min.js'></javascript>");
function orderPic(){
	 this.maxNum = "1";//上传的图片最大数量
	 this.currentFormId = "";//当前formID
	 this.currentFormObj = "";//当前form对象
	 this.currentFileObj = "";//当前文件对象
	 this.currentButtonObj = "";//当前提交上传按钮对象
	 this.currentFileUploadObjList = [];//当前input：change文件对象
	 this.currentFileUploadUrl = "";//文件上传URL
	 this.inputParams = {};//当前ajax
	 
	 this.allowPicType = ["image/jpg","image/png","image/jpeg"];//允许上传的图片后缀
}
//数据配置方法
orderPic.prototype.config = function(object){
	this.currentFormId = object.formId?object.formId:"";//form表单id
	this.maxNum = object.maxNum?object.maxNum:"";//上传文件数最大值
	this.currentFileUploadUrl = object.UploadUrl?object.UploadUrl:"";//上传url
	this.inputParams = object.otherInputParams?object.otherInputParams:{};//其他input表单对象
	
	if(this.currentFormId!=''){
		this.currentFormObj = $("#"+this.currentFormId);
		this.currentFileObj = this.currentFormObj.children("input[type='file']");
		this.currentButtonObj = this.currentFormObj.children("input[type='button']");
	}
	
}

//注册form事件
orderPic.prototype.registerForm = function(object){
	var _this = this;
	//配置信息
	this.config(object);
	//当前已上传图片数量
	this.currentPic();
	/*
	 * 事件注册
	 * 
	 * */
	//选择图片并预览
	this.currentFileObj.bind("change",function(){
		_this.previewPic(this);//图片预览
	});
	//点击上传图片按钮
	this.currentButtonObj.bind("click",function(){
		_this.ajaxAddPic(0);
	});
	//删除预览图片
	this.registerDelPreviewPic();
}


//图片预览
orderPic.prototype.previewPic = function(obj){
	//获取上传对象
	var arrFiles = this.getFileList(obj);
	//清空预览图片
	this.currentFormObj.children("img.preview_pic").remove();
	//图片预览
	for(var i=0;i<arrFiles.length;i++){
		if($.inArray(arrFiles[i].type,this.allowPicType)){
			var reader = new FileReader();
			reader.picNname = arrFiles[i].name;
			reader.readAsDataURL(arrFiles[i]);
	        reader.onload = function(evt){
	        	$(obj).before("<img preview='1' class='preview_pic' preview_name='"+evt.target.picNname+"' src='"+evt.target.result+"' style='width:100px;'/>");
	        }
		}
       
	}//for
	
}
//ajax上传图片
orderPic.prototype.ajaxAddPic = function(index){
	var _this = this,index = 0,successLength = 0,totalLength = 0;
	totalLength = this.currentFileUploadObjList.length;
	var start_time = setInterval(function(){
		if(_this.currentFileUploadObjList.length<=0){
			clearInterval(start_time);
			return;
		}
		var obj = {};//input参数对象
		$(obj).attr(_this.currentFileObj.attr('name'), _this.currentFileUploadObjList[0]);
		var formData = _this.createNewForm(obj);
		var picName = _this.currentFileUploadObjList[0].name;
		$.ajax({
			url:_this.currentFileUploadUrl,
		    type: 'POST',
		    async: false,
		    cache: false,
		    data: formData,
		    processData: false,
		    contentType: false,
		    success:function(F){
		    	//index++;
		    	successLength++;
		    	_this.uploadPicSuccess({"picName":picName,"total":totalLength,"successLength":successLength});
		    },
		    error:function(){
		    	alert('error');
		    }
		});
	},1000);
}

//注册删除预览图片
orderPic.prototype.registerDelPreviewPic = function(){
	var _this = this;
	$("img.preview_pic").live('click',function(){
		_this.deleteFileListElement($(this).attr('preview_name'));
		$(this).remove();
	});
	
}
//-------------------------------工具方法---------------------------------------------------------


//是否允许再次上传照片
orderPic.prototype.isAllowUpload = function(){
	if(this.maxNum<=this.currentPic())return false;
	return true;
}

//获取【待上传‘文件’】对象
orderPic.prototype.getFileList = function(obj){
	var arrPicName = this.getFileName();
	var arrFiles = [];
	if(obj=='')return this.currentFileUploadObjList;
	for(var i=0;i<obj.files.length;i++){
		if($.inArray(obj.files[i].name,arrPicName)==-1){
			this.currentFileUploadObjList.push(obj.files[i]);
		}
		
	}
	return this.currentFileUploadObjList;
}
//删除文件上传对象,picName，根据图片名称删除
orderPic.prototype.deleteFileListElement = function(picName){
	var arrFiles = [];
	if(picName=='' || $.inArray(picName,this.getFileName())==-1)return ;
	$.each(this.currentFileUploadObjList,function(i,obj){
		if(picName!=obj.name)arrFiles.push(obj);
	});
	this.currentFileUploadObjList = arrFiles;
}
//已上传图片数量
orderPic.prototype.currentPic = function(){
	var totalPicUpload = 0;
	$.each(this.currentFileObj,function(i,obj){
		if($(obj).attr("isUpload")==1)totalPicUpload++;
	});
	return totalPicUpload;
}

//获取【待上传】文件名称
orderPic.prototype.getFileName = function(){
	var arrPicName = [];
	$.each(this.currentFileUploadObjList,function(i,obj){
		arrPicName.push(obj.name);
	});
	return arrPicName;
}
//上传图片成功后回调方法
orderPic.prototype.uploadPicSuccess = function(obj){
	this.deleteFileListElement(obj.picName);//置空上传成功的图片对象
	
	$("#process").css({"width":100*(obj.successLength/obj.total)+"%"});//进度条
}

//创建Newform对象
orderPic.prototype.createNewForm = function(obj){
	var formData = new FormData();
	for(var i in this.inputParams){
		formData.append(i,this.inputParams[i]);
	}
	for(var i in obj){
		formData.append(i,obj[i]);
	}
	return formData;
	//var picName = _this.currentFileUploadObjList[0].name;
	//formData.append(_this.currentFileObj.attr('name'),_this.currentFileUploadObjList[0]);
}




