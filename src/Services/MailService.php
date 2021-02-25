<?php

namespace App\Services;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;


class MailerService {

    public function __construct()
    {

    }

    public function PostMail(string $to, string $subject, array $context = [])
    {
        $email = (new TemplatedEmail())
                ->from('mon-dossier-immo@support.com')
                ->to($to)
                ->replyTo('mon-dossier-immo@support.com')
                ->subject($subject)
                ->context([
                     'context' => $context
                    ])
                ->htmlTemplate('mail_template/signup/index.html.twig')
                ;

        $this->mailer->send($email);
    }

}