<?php
/**
 * 腾讯滑块验证码辅助函数
 * 
 * @date: 2017年8月30日
 * @author: forrest.tu
 * @email: forrest.tu@transn.com
 */

/**
 * 调用腾讯滑块验证码
 * 
 * 
 * @param string $business_id 场景ID
 * @param number $width 验证码宽度
 * @param number $height 验证码高度
 * @param string $autoload 是否自动载入，单页调用多个有BUG，需要手动调用
 * @return string
 */
function tcaptcha($business_id = "captcha", $width = 320, $height = 40, $autoload = true) {
	static $time = 0;
	$tcaptcha = new \tcaptcha\tcaptcha($business_id);
	$js_url = $tcaptcha->getJsUrl();

	$tcaptcha_js = '';
	if($time == 0) {
		$tcaptcha_js = '<script src="'.$js_url.'"></script>';
		$time++;
	}
	$tcaptcha_js .= "
	<input type=\"hidden\" datatype=\"*\" sucmsg=\"\" nullmsg=\"请拖动滑块完成拼图\" id=\"ticket{$business_id}\" name=\"ticket\" value=\"\">
	<div id=\"T{$business_id}\" style=\"width:{$width}px;height:{$height}px;\" ></div>
	<script>
	function T{$business_id}_load(){
		capDestroy();
		capInit(document.getElementById(\"T{$business_id}\"), {callback:function(retJson) {
		if (retJson.ret == 0) {
			if(typeof({$business_id}_callback) == 'function') {$business_id}_callback();
			document.getElementById(\"ticket{$business_id}\").value = retJson.ticket;
			$('#ticket{$business_id}').parent().find('.empty,.Validform_checktip').length && $('#ticket{$business_id}').parent().find('.empty,.Validform_checktip').html('');
		}
	}, themeColor:'1aba79'});
	}";
	if($autoload){
		$tcaptcha_js .= "T{$business_id}_load()";
	}
	$tcaptcha_js .= "</script>";
	
	return $tcaptcha_js;
}

/**
 * 验证码验证
 * 
 * 
 * @param string $ticket 验证码校验串
 * 
 * @throws \think\Exception
 */
function tcaptcha_check($ticket) {
	$tcaptcha = new \tcaptcha\tcaptcha();
	$result = $tcaptcha->Check($ticket);
	
	if($result['code'] !== 0) {
		throw new \think\Exception('请点击验证码完成验证');
	}
}

/**
 * 获取滑块js地址
 * @param string $business_id
 * @return mixed
 */
function getTcaptchaJs($business_id = "captcha"){
    $tcaptcha = new \tcaptcha\tcaptcha($business_id);
    $js_url = $tcaptcha->getJsUrl($business_id);
    return $js_url;
}
