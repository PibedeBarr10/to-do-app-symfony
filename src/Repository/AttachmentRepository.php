<?php

namespace App\Repository;

use App\Entity\Attachment;
use App\Entity\Task;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Attachment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Attachment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Attachment[]    findAll()
 * @method Attachment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AttachmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Attachment::class);
    }

    public function save(Attachment $attachment)
    {
        $this->_em->persist($attachment);
        $this->_em->flush();
    }

    public function remove(Attachment $attachment)
    {
        $this->_em->remove($attachment);
        $this->_em->flush();
    }

    public function add_file(Task $task, string $unique_name, string $originalFilename)
    {
        $attachment = new Attachment();
        $attachment->setTask($task);
        $attachment->setUniqueName($unique_name);
        $attachment->setName($originalFilename);
        $attachment->setCreationDate(new DateTime());

        $this->save($attachment);
        $task->addAttachment($attachment);
    }

    // /**
    //  * @return Attachment[] Returns an array of Attachment objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Attachment
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
