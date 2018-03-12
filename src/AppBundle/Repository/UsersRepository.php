<?php


namespace AppBundle\Repository;

use AppBundle\Entity\Users;
use Doctrine\ORM\EntityRepository;

class UsersRepository extends EntityRepository
{
    /**
     * Получить всех пользователей с определенной ролью
     *
     * @param $role
     * @param bool $collection
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllUsersDependenceRole($role, $collection = true)
    {
        $users = $this
            ->_em
            ->getRepository(Users::class)
            ->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%' . $role . '%');

        if ($collection) {
            $users = $users
                ->getQuery()
                ->getResult();
        }

        return $users;
    }
}