<?php


namespace App\Service;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class Mailer
{
    protected MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendMail(
        string $from,
        string $to,
        string $subject,
        string $html,
        string $pathToFile = null
    ): void
    {
        $email = (new Email())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->html($html);

        if (isset($pathToFile))
        {
            $email->attachFromPath($pathToFile);
        }

        $this->mailer->send($email);
    }
}