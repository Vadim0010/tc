<?php

namespace AppBundle\Repository;

use AppBundle\Entity\LessonsUsers;
use Doctrine\ORM\EntityRepository;

class LessonsUsersRepository extends EntityRepository
{
    public function getListLessonsForTeacher($user, $date)
    {
        return $this
            ->_em
            ->createQueryBuilder()
            ->select('lg')
            ->from(LessonsUsers::class, 'lg')
            ->where('lg.user = :user')
            ->andWhere('lg.createdAt > :date')
            ->setParameters(['user' => $user, 'date' => $date])
            ->orderBy('lg.createdAt', 'desc')
            ->getQuery()
            ->getResult();
    }
}
