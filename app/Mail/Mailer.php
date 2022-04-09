<?php

namespace App\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    public function sendEmail($user, $token)
    {
        $subject = 'Forgot Password';
        //$data = "Hi, " . $user->first_name . " " . $user->last_name . "<br>Your Password Reset Link:<br>http://localhost:8000/resetPassword/" . $token;
        $data = "Hi, " . $user->first_name . " " . $user->last_name . "<br>Your Password Reset Token:<br>" . $token;

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = env('MAIL_HOST');
            $mail->SMTPAuth   = true;
            $mail->Username   = env('MAIL_USERNAME');
            $mail->Password   = env('MAIL_PASSWORD');
            $mail->SMTPSecure = 'tls';
            $mail->Port       = env('MAIL_PORT');
            $mail->setFrom(env('MAIL_USERNAME'), env('MAIL_FROM_NAME'));
            $mail->addAddress($user->email);
            $mail->isHTML(true);
            $mail->Subject =  $subject;
            $mail->Body    = $data;
            if ($mail->send()) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return back()->with('error', 'Message could not be sent.');
        }
    }
}
