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
        string $pathToFile = null,
        string $context = null
    ): void
    {
        $email = (new TemplatedEmail())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->htmlTemplate($htmlTemplate);

        if (isset($pathToFile)) {
            $email->attachFromPath($pathToFile);
        }

        if (isset($context)) {
            $email->context([
                'context' => $context,
            ]);
        }

        $this->mailer->send($email);
    }
}