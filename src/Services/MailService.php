<?php

namespace App\Services;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;


class MailService {

    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
        $this->mailSupport = 'support@mon-dossier-immo.com';
    }

    /**
     * PostMail function
     * @param string $to
     * @param string $subject
     * @param string $template
     * @param array $context
     * @return void
     */
    public function PostMail(string $to, string $subject, string $template, array $context = [])
    {
        $email = (new TemplatedEmail())
                ->from($this->mailSupport)
                ->to($to)
                ->replyTo($this->mailSupport)
                ->subject($subject)
                ->context([
                     'context' => $context
                    ])
                ->htmlTemplate($template)
                ;

        $this->mailer->send($email);
    }

    /**
     * GetMail function
     *
     * @param string $from
     * @param string $subject
     * @param string $template
     * @param array $context
     * @return void
     */
    public function GetMail(string $from, string $subject, string $template, array $context = [])
    {
        $email = (new TemplatedEmail())
                ->from($from)
                ->to($this->mailSupport)
                ->replyTo($from)
                ->subject($subject)
                ->context([
                     'context' => $context
                    ])
                ->htmlTemplate($template)
                ;

        $this->mailer->send($email);
    }

}