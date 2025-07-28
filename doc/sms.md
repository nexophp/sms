## 短信

短信服务由第三方提供

- 默认为直连
- [阿里云短信](https://dysms.console.aliyun.com/)
- [腾讯云短信](https://console.cloud.tencent.com/smsv2/app-manage)


调用事例

~~~
//发送邮箱验证码
$code = rand(100000, 999999); 
add_sms_template('bind_account', "绑定邮箱", '绑定邮箱验证', '您正在绑定邮箱，您的验证码是<b>{code}</b>,5分钟有效，如非本人请求请忽略。');
cache("bind_account_{$phone}", $code, 300);

send_sms('bind_account', $phone, [
    'code' =>  $code,
]); 
~~~



~~~
send_sms($phone, $template_code, $replace = [])
~~~

添加模板

~~~
add_sms_template($code, $name, $title, $content)
~~~ 

用法与邮箱类似

验证短信时模板代码是 `login` ，需要验证码的变量是 `{code}`