<?php


namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
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
        string $htmlTemplate,
        string $pathToFile = null
    ): void
    {
        $email = (new TemplatedEmail())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->htmlTemplate($htmlTemplate);

        if (isset($pathToFile))
        {
            $email->attachFromPath($pathToFile);
        }

        $this->mailer->send($email);
    }
}