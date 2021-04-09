<?php


namespace App\Service\MailerApp;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SendRequest
{
    private TasksArrays $tasksArrays;
    private ParameterBagInterface $parameterBag;

    public function __construct(TasksArrays $tasksArrays, ParameterBagInterface $parameterBag)
    {
        $this->tasksArrays = $tasksArrays;
        $this->parameterBag = $parameterBag;
    }

    public function sendRequest(UserInterface $admin, User $user): ResponseInterface
    {
        [$tasks_array, $body] = $this->tasksArrays->getTasksArrays($user->getId());

        $httpClient = HttpClient::create([
            'auth_basic' => [
                $admin->getUsername(),
                $this->parameterBag->get('mailer_app_password')
            ]
        ]);
        $response = $httpClient->request('POST',
            'http://www.mailer-app.com/sendMail',
            [
                'json' => [
                    'email' => $user->getUsername(),
                    'body' => $body,
                    'file_data' => $tasks_array
                ]
            ]
        );

        return $response;
    }
}