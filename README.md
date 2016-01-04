# php_mail



```
require __DIR__ . "/../vendor/autoload.php";
use Mail\Smtp;

$mail = new Smtp();

$mail->host = 'smtp.163.com';
$mail->user = 'xxx@163.com';
$mail->pass = 'xxx';

$mail->debug = true;
$mail->nickname = 'fifths';
$to = "xxx@qq.com";
$subject = 'it is subject';
$body = 'it is body';
$data = $mail->sendMail($to, $body, $subject);
print_r($data);
```

```
succeed connect to smtp.163.com:25
220 163.com Anti-spam GT for Coremail System (163com[20141201])
250 OK
334 UGFzc3dvcmQ6
235 Authentication successful
250 Mail OK
250 Mail OK
354 End data with <CR><LF>.<CR><LF>
250 Mail OK queued as smtp7,C8CowEA5d0bRGYpW8O4AAA--.501S2 1451891154
221 Bye
```
