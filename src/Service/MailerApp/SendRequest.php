<?php


namespace App\Service\MailerApp;

use App\Entity\User;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\HttpClient;

class SendRequest
{
    private TasksArrays $tasksArrays;
    private ParameterBagInterface $parameterBag;

    public function __construct(TasksArrays $tasksArrays, ParameterBagInterface $parameterBag)
    {
        $this->tasksArrays = $tasksArrays;
        $this->parameterBag = $parameterBag;
    }

    public function sendRequest(User $user): string
    {
        [$tasks_array, $body] = $this->tasksArrays->getTasksArrays($user->getId());

        $httpClient = HttpClient::create([
            'auth_basic' => [
                $this->parameterBag->get('mailer_app_username'),
                $this->parameterBag->get('mailer_app_password')
            ]
        ]);
        $response = $httpClient->request('POST',
            $this->parameterBag->get('api_url'),
            [
                'json' => [
                    'email' => $user->getUsername(),
                    'body' => $body,
                    'file_data' => $tasks_array
                ]
            ]
        );

        if (200 !== $response->getStatusCode()) {
            throw new \Exception(json_decode($response->getContent()), $response->getStatusCode());
        }

        return json_decode($response->getContent());
    }
}