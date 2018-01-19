# think-tcaptcha
thinkphp5 腾讯验证码类库

## 安装
> composer require tj646/think-tcaptcha


##使用

###在配置扩展目录添加配置文件tcaptcha_config.php填写你的配置参数

~~~
<?php 
 return [
	'secret_id' => '***',
	'secret_key' => '***'
];

~~~
###模板里输出验证码

~~~
<div>{:tcaptcha('register',320,40,0)}</div>
~~~

### 手动验证
~~~
if(!tcaptcha_check($ticket)){
 //验证失败
};
~~~