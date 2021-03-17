<?php

namespace App\Command;

use App\Entity\Task;
use App\Controller\TaskController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class BackupCSV extends Command
{
    protected static $defaultName = 'app:sendCSV';

    public function __construct(MailerInterface $mailer, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->mailer = $mailer;
        $this->controller = new TaskController();
        $this->em = $em;
    }

    protected function configure()
    {
        $this->setDescription('Sends email with all tasks')
        ->setHelp('This command sends email with all tasks from database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->sendCSV($this->mailer);

        $message = sprintf("Mail wysłany");

        $output->writeln($message);
        return 0;   // Return value of "App\Command\BackupCSV::execute()" must be of the type int, "null" returned.
    }

    public function sendCSV(MailerInterface $mailer)
    {
        $file = fopen('tasks.csv', 'w');

        $repository = $this->em->getRepository(Task::class);
        $tasks = $repository->findAll();
        
        foreach ($tasks as $task)
        {
            $id = $task->getId();
            $title = $task->getTitle();

            $deadline = $task->getDeadline();
            $date = $deadline->format('d-m-Y');

            $user = $task->getUserId();
            $userid = $user->getId();

            $checked = $task->getChecked();

            $array =[$id, $title, $date, $userid, $checked];
            // dump($array);

            fputcsv($file, $array);
        }

        fclose($file);

        $email = (new Email())
            ->from('hello@example.com')
            ->to('you@example.com')
            ->subject('Codzienny backup zadań')
            ->html('<p>W załączniku spis wszystkich zadań znajdujących się w bazie danych.</p>')
            ->attachFromPath('tasks.csv');

        $mailer->send($email);
    }
}