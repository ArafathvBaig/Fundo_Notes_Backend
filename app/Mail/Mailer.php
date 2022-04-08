<?php

namespace App\Mail;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    public function sendEmail($email, $token)
    {
        $subject = 'Forgot Password';
        $data = "Your password Reset Link" . $token;

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = env('MAIL_HOST');
            $mail->SMTPAuth   = true;
            $mail->Username   = env('MAIL_USERNAME');
            $mail->Password   = env('MAIL_PASSWORD');
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;
            $mail->setFrom(env('MAIL_USERNAME'), env('MAIL_FROM_NAME'));
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject =  $subject;
            $mail->Body    = $data;
            $dt = $mail->send();
            sleep(3);

            if ($dt)
                return true;
            else
                return false;
        } catch (Exception $e) {
            return back()->with('error', 'Message could not be sent.');
        }
    }
}
