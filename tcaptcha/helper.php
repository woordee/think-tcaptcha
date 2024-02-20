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
function tcaptcha($business_id = "captcha", $width = 320, $height = 40, $autoload = true)
{
    static $time = 0;
    $tcaptcha = new \tcaptcha\tcaptcha($business_id);
    if ($business_id == 'mobileRegister') {
        $js_url = $tcaptcha->getJsUrl('mobile');
    } else {
        $js_url = $tcaptcha->getJsUrl();
    }


    $tcaptcha_js = '';
    if ($time == 0) {
        $tcaptcha_js = "<script src=\"" . $js_url . "\"></script>";
        $time++;
    }
    $tcaptcha_js .= "
	<input type=\"hidden\" datatype=\"*\" sucmsg=\"\" nullmsg=\"请拖动滑块完成拼图\" id=\"ticket{$business_id}\" name=\"ticket\" value=\"\">
	<div id=\"T{$business_id}\" style=\"width:{$width}px;height:{$height}px;\" ></div>
	<script>
	function T{$business_id}_load(clickId){
	    if(typeof(clickId) == \"undefined\"){
	        clickId = \"sendSms\";
	    }
		capDestroy();
		capInit(document.getElementById(\"T{$business_id}\"), {callback:function(retJson) {
		if (retJson.ret == 0) {
			document.getElementById(\"ticket{$business_id}\").value = retJson.ticket;
			$(\"#ticket{$business_id}\").parent().find(\".empty,.Validform_checktip\").length && $(\"#ticket{$business_id}\").parent().find(\".empty,.Validform_checktip\").html(\"\");
			$(\"#\"+clickId).trigger(\"click\");
		}
	}, themeColor:\"1aba79\"});
	}";
    if ($autoload) {
        $tcaptcha_js .= "T{$business_id}_load()";
    }
    $tcaptcha_js .= "</script>";

    return $tcaptcha_js;
}

/**
 * 调用腾讯滑块验证码
 * 返回数组 add by mark
 * @param string $business_id 场景ID
 * @param number $width 验证码宽度
 * @param number $height 验证码高度
 * @param string $autoload 是否自动载入，单页调用多个有BUG，需要手动调用
 * @return string
 */
function tcaptchaArr($business_id = "captcha", $width = 320, $height = 40, $autoload = true)
{
    static $time = 0;
    $tcaptcha = new \tcaptcha\tcaptcha($business_id);
    if ($business_id == 'mobileRegister') {
        $js_url = $tcaptcha->getJsUrl('mobile');
    } else {
        $js_url = $tcaptcha->getJsUrl();
    }

    $returnArr = [];

    $tcaptcha_html = '';
    $tcaptcha_js = '';
    if ($time == 0) {
        $tcaptcha_js = '<script src="' . $js_url . '"></script>';
        $time++;
    }
    $tcaptcha_html .= "
	<input type=\"hidden\" datatype=\"*\" sucmsg=\"\" nullmsg=\"请拖动滑块完成拼图\" id=\"ticket{$business_id}\" name=\"ticket\" value=\"\">
	<div id=\"T{$business_id}\" style=\"width:{$width}px;height:{$height}px;\" ></div>";
    $tcaptcha_js .= "<script>
	function T{$business_id}_load(){
		capDestroy();
		capInit(document.getElementById(\"T{$business_id}\"), {callback:function(retJson) {
		if (retJson.ret == 0) {
			document.getElementById(\"ticket{$business_id}\").value = retJson.ticket;
			$('#ticket{$business_id}').parent().find('.empty,.Validform_checktip').length && $('#ticket{$business_id}').parent().find('.empty,.Validform_checktip').html('');
		}
	}, themeColor:'1aba79'});
	}";
    if ($autoload) {
        $tcaptcha_js .= "T{$business_id}_load()";
    }
    $tcaptcha_js .= "</script>";

    $returnArr['html'] = $tcaptcha_html;
    $returnArr['js'] = $tcaptcha_js;

    return $returnArr;
}

/**
 * 验证码验证
 *
 *
 * @param string $ticket 验证码校验串
 *
 * @throws \think\Exception
 */
function tcaptcha_check($ticket)
{
    $tcaptcha = new \tcaptcha\tcaptcha();
    $result = $tcaptcha->Check($ticket);

    if ($result['code'] !== 0) {
        throw new \think\Exception('请拖动滑块验证');
    }
}

/**
 * 获取滑块js地址
 * @param string $business_id
 * @return mixed
 */
function getTcaptchaJs($business_id = "captcha")
{
    $tcaptcha = new \tcaptcha\tcaptcha($business_id);
    $js_url = $tcaptcha->getJsUrl($business_id);
    return $js_url;
}


/**
 * 阿里云验证码验证
 */
use afs\Request\V20180112 as Afs;

function aliCaptchaCheck($sessionId, $token, $sig, $scene)
{
    require_once Env::get('extend_path') . 'captcha/aliyun-php-sdk-core/Config.php';
    $iClientProfile = DefaultProfile::getProfile("cn-hangzhou", config('aliyun_captcha_key'), config('aliyun_captcha_scene'));
    $client = new DefaultAcsClient($iClientProfile);
    DefaultProfile::addEndpoint("cn-hangzhou", "cn-hangzhou", "afs", "afs.aliyuncs.com");
    $request = new Afs\AuthenticateSigRequest();
    $request->setSessionId($sessionId);// 必填参数，从前端获取，不可更改
    $request->setToken($token);// 必填参数，从前端获取，不可更改
    $request->setSig($sig);// 必填参数，从前端获取，不可更改
    $request->setScene($scene);// 必填参数，从前端获取，不可更改
    $request->setAppKey("FFFF00000000017E322A");//必填参数，后端填写
    $request->setRemoteIp(request()->ip());//必填参数，后端填写
    $response = $client->getAcsResponse($request);//response的code枚举：100验签通过，900验签失败
    if ($response->Code !== 100) {
        throw new \think\Exception('请拖动滑块验证!');
    }
    return $response->Code;
}


/**
 * 阿里云验证码显示
 * @param string $business_id
 * @param string $callbackId
 * @param int $width
 * @param int $height
 * @return string
 */
use think\Request;
function aliTcaptcha($business_id = "login",$callbackId='' ,$width = 370, $height = 50){
    if(Request()->isMobile()){
        return aliWapTcaptcha($business_id,$callbackId,$width);
    }
    static $time = 0;
    $tcaptcha_js = '';
    if ($time == 0) {
        $tcaptcha_js = "<script src='/static/alicaptcha.js' ></script>";
        $time++;
    }
    $tcaptcha_js.="<script>
                        window.onload = function(){
                            CAPTCHA.check('".$business_id."','".$callbackId."',$width,$height);
                        }
                    </script>";
    return $tcaptcha_js;
}


/**
 * 移动端阿里验证码
 * @param string $business_id
 * @param string $callbackId
 * @param int $width
 * @return string
 */
function aliWapTcaptcha($business_id = "register",$callbackId='' ,$width = 380){
    static $time = 0;
    $tcaptcha_js = '';
    if ($time == 0) {
        $tcaptcha_js = "<script src='/static/aliwapcaptcha.js' ></script>";
        $time++;
    }
    $tcaptcha_js.="<script>
                        window.onload = function(){
                            CAPTCHA.check('".$business_id."','".$callbackId."',$width);
                        }
                    </script>";
    return $tcaptcha_js;
}


/**
 * 获取阿里云验证码Js
 */
function getAliTcaptchaJs(){
    if(Request()->isMobile()){
        return "<script src='/static/aliwapcaptcha.js' ></script>";
    }else{
        return "<script src='/static/alicaptcha.js' ></script>";
    }
}