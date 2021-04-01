<?php


namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Mailer\MailerInterface;

class Mailer
{
    private MailerInterface $mailer;
    private ParameterBagInterface $parameterBag;

    public function __construct(
        MailerInterface $mailer,
        ParameterBagInterface $parameterBag
    ) {
        $this->mailer = $mailer;
        $this->parameterBag = $parameterBag;
    }

    public function sendMail(
        string $to,
        string $subject,
        string $htmlTemplate,
        string $pathToFile = null,
        string $context = null
    ): void
    {
        $email = (new TemplatedEmail())
            ->from($this->parameterBag->get('system_mail_from'))
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