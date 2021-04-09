<?php


namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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
        array $parameters = []
    ): void
    {
        $email = (new TemplatedEmail())
            ->from($this->parameterBag->get('system_mail_from'))
            ->to($to)
            ->subject($subject)
            ->htmlTemplate($htmlTemplate);

        if ($parameters) {
            $email->context([
                'parameters' => $parameters,
            ]);
        }

        $this->mailer->send($email);
    }

    public function sendMailWithAttachment(
        string $to,
        string $subject,
        string $htmlTemplate,
        string $pathToFile,
        array $parameters = []
    ): void
    {
        $email = (new TemplatedEmail())
            ->from($this->parameterBag->get('system_mail_from'))
            ->to($to)
            ->subject($subject)
            ->htmlTemplate($htmlTemplate)
            ->attachFromPath($pathToFile);

        if ($parameters) {
            $email->context([
                'parameters' => $parameters,
            ]);
        }

        $this->mailer->send($email);
    }
}