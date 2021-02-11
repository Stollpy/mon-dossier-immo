<?php

namespace App\Services;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;


class MailerService {

    public function __construct()
    {

    }

    public function PostMail(User $data, string $subject, array $context = [])
    {
        $userMail = $data->getEmail();
        $email = (new TemplatedEmail())
                ->from('mon-dossier-immo@support.com')
                ->to($userMail)
                ->replyTo('mon-dossier-immo@support.com')
                ->subject($subject)
                ->context([
                     'data' => $data
                    ])
                ->htmlTemplate('mail_template/signup/index.html.twig')
                ;

        $this->mailer->send($email);
    }

}