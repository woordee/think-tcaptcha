<?php
/**
 * 腾讯验证码
 * 
 * 
 */
namespace tcaptcha;

class tcaptcha {
	private $gateUrl = 'csec.api.qcloud.com/v2/index.php';
	private $IS_HTTPS = 0; // 返回的JavaScript中是否使用HTTPS 0：HTTP 1：HTTPS
	private $CAPTACHA_TYPE = 4; // 验证码类型
	private $DISTURB_LEVEL = 1; // 验证码干扰程度
	private $CLIENT_TYPE = 2; // 客户端类型 1：手机Web页面 2：PCWeb页面 4：APP
	private $BUSINESS_ID = 1; // 业务ID 网站或应用在多个业务中使用此服务，通过此ID区分统计数据
	private $VERIFY_TYPE = 0;
	private $SECRET_ID; // ID
	private $SECRET_KEY;
	// 密钥
	public function __construct() {
		$this->SECRET_ID = config('tcaptcha_config.secret_id');
		$this->SECRET_KEY = config('tcaptcha_config.secret_key');
	}

	public function getJsUrl($source='') {
		$url = $this->makeURL('GET', 'CaptchaIframeQuery', 'sz', $this->SECRET_ID, $this->SECRET_KEY, [
			/* 行为信息参数 */
			'userIp' => request()->ip(0,true),
			'captchaType' => $this->CAPTACHA_TYPE,
			'disturbLevel' => $this->DISTURB_LEVEL,
			'isHttps' => $this->IS_HTTPS,
			'clientType' => $source=='mobile'?1:$this->CLIENT_TYPE,

            /* 其他信息参数 */
            'businessId' => $this->BUSINESS_ID
		]);
		$result = $this->sendRequest($url);
		$jsUrl = $result['url'];
		
		return $jsUrl;
	}

	public function Check($ticket) {
		$url = $this->makeURL('GET', 'CaptchaCheck', 'sz', $this->SECRET_ID, $this->SECRET_KEY, [
			/* 行为信息参数 */
			'userIp' => request()->ip(0,true),

            /* 验证码信息参数 */
            'captchaType' => $this->CAPTACHA_TYPE,
			'ticket' => $ticket,
			'verifyType' => $this->VERIFY_TYPE, // 验证类型，默认填0，如果js页面参数配置了firstvrytype=2，启动了滑动验证功能，则该值必填，而且必须跟firstvrytype的值保持一致
			/* 其他信息参数 */
			'businessId' => $this->BUSINESS_ID
		]);
		$result = $this->sendRequest($url);
		return $result;
	}

	public function sendRequest($url, $method = 'POST') {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		if(false !== strpos($url, "https")) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		}
		$resultStr = curl_exec($ch);
		$result = json_decode($resultStr, true);
		
		return $result;
	}

	public function makeURL($method, $action, $region, $secretId, $secretKey, $args) {
		$args['Nonce'] = (string)rand(0, 0x7fffffff);
		$args['Action'] = $action;
		$args['Region'] = $region;
		$args['SecretId'] = $secretId;
		$args['Timestamp'] = (string)time();
		
		ksort($args);
		$args['Signature'] = base64_encode(hash_hmac('sha1', $method . $this->gateUrl . '?' . $this->makeQueryString($args, false), $secretKey, true));
		
		return 'https://' . $this->gateUrl . '?' . $this->makeQueryString($args, true);
	}

	public function makeQueryString($args, $isURLEncoded) {
		$arr = array();
		foreach($args as $key => $value) {
			if(!$isURLEncoded) {
				$arr[] = "$key=$value";
			} else {
				$arr[] = $key . '=' . urlencode($value);
			}
		}
		return implode('&', $arr);
	}
}
