<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function save(Task $task)
    {
        $this->_em->persist($task);
        $this->_em->flush();
    }

    public function remove(Task $task)
    {
        $this->_em->remove($task);
        $this->_em->flush();
    }

    public function update()
    {
        $this->_em->flush();
    }

    public function findUserTasks(int $id)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT t FROM App:Task t
                JOIN t.user_id u
                WHERE t.user_id = :id'
            )->setParameter('id', $id);
        
        return $query->getResult();
    }
}
