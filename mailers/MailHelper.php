<?php

require_once(dirname(__FILE__).'/PHPMailer/SMTP.php');
require_once(dirname(__FILE__).'/PHPMailer/PHPMailer.php');

class MailHelper
{
    public static function send(array $from, array $to, $subject, $htmlBody, $plainBody = '')
    {
        // prepare data
        if (trim($plainBody)) {
            $plainBody = strip_tags($htmlBody);
        }

        $mail = new PHPMailer;

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->Username = 'robot@prflr.org';
        //$mail->Username = 'no-reply@2hive.org';
        $mail->Password = 'robot06539010';
        $mail->SMTPSecure = 'tls';

        $mail->addReplyTo($from[0]);
        $mail->From = $from[0];
        if (isset($from[1])) {
            $mail->FromName = $from[1];
        }
        foreach($to as $recipient) {
            if (!isset($recipient['email'])) continue;
            $mail->addAddress($recipient['email'], (isset($recipient['name']) ? $recipient['name'] : ''));
        }
        $mail->isHTML(true);

        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $plainBody;

        if (!$mail->send()) {
            throw new Exception($mail->ErrorInfo);
        }
    }
}
