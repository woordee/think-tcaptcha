# think-tcaptcha
thinkphp5 腾讯验证码类库

## 安装
> composer require tj646/think-tcaptcha


##使用

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