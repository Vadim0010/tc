<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Groups;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;

class GroupsRepository extends EntityRepository
{
    /**
     * @param string $role
     * @param $user
     * @param string|null $current_date
     * @param string $operation
     * @param bool $completed
     * @param bool $collection
     * @param string $sort
     * @param string $order
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllGroupsForUser(
        string $role = 'listener',
        $user,
        string $current_date = null,
        string $operation = '',
        bool $completed = false,
        bool $collection = false,
        string $sort = 'createdAt',
        string $order = 'DESC'
    )
    {
        $groups = $this
            ->_em
            ->getRepository(Groups::class)
            ->createQueryBuilder('g')
            ->leftJoin('g.course', 'course')
            ->addSelect('course')
//            ->leftJoin('g.teacher', 'teacher')
//            ->leftJoin('g.curator', 'curator')
//            ->leftJoin('course.lessons', 'lessons')
//            ->leftJoin('g.lessonsGroup', 'lessonsGroup')
//            ->addSelect('teacher')
//            ->addSelect('curator')
//            ->addSelect('lessons')
//            ->addSelect('lessonsGroup')
        ;

        if ($role == 'teacher') {
            $groups = $groups
                ->innerJoin('g.teacher', 'u');
        } elseif ($role == 'curator') {
            $groups =$groups
                ->innerJoin('g.curator', 'u');
        } elseif ($role == 'listener') {
            $groups = $groups
                ->innerJoin('g.users', 'u');
        } elseif ($role == 'admin') {
            if ($completed) {
                $groups = $groups
                    ->andWhere('g.isCompleted = 1');
            } elseif ($operation) {
                $groups = $groups
                    ->andWhere('g.isCompleted = 0')
                    ->andWhere('g.createdAt ' . $operation . ' :date')
                    ->setParameters([
                        'date' => $current_date
                    ]);
            }

            $groups = $groups
                ->addOrderBy('g.' . $sort, $order);

            if ($collection) {
                $groups = $groups
                    ->getQuery()
                    ->getResult();
            }

            return $groups;
        }

        $groups = $groups
            ->where('u.id = :user');

        if ($completed) {
            $groups = $groups
                ->andWhere('g.isCompleted = 1')
                ->setParameter('user', $user);
        } elseif ($operation) {
            $groups = $groups
                ->andWhere('g.isCompleted = 0')
                ->andWhere('g.createdAt ' . $operation . ' :date')
                ->setParameters([
                    'date' => $current_date,
                    'user' => $user
                ]);
        } else {
            $groups = $groups
                ->setParameter('user', $user);
        }

        $groups = $groups
            ->addOrderBy('g.' . $sort, $order);

        if ($collection) {
            $groups = $groups
                ->getQuery()
                ->getResult();
        }

        return $groups;
    }

    public function searchDqlGroups($data)
    {
        $qb = $this->createQueryBuilder('g');
        $accessor = PropertyAccess::createPropertyAccessor();
        $course = $accessor->getValue($data, '[course]');
        $name  = $accessor->getValue($data, '[name]');
        $teacher = $accessor->getValue($data, '[teacher]');

        $groups = $qb
            ->leftJoin('g.course', 'course')
            ->leftJoin('g.teacher', 'teacher')
            ->addSelect('course')
            ->addSelect('teacher')
        ;

        if ($course) {
            $groups
                ->andWhere('g.course = :course')
                ->setParameter('course', $course)
            ;
        }

        if ($name) {
            $orX = $qb->expr()->orX();

            $orX
                ->add(
                    $qb->expr()->like('LOWER(g.number)', ':name')
                )
                ->add(
                    $qb->expr()->like('LOWER(g.title)', ':name')
                )
            ;

            $groups
                ->andWhere($orX)
                ->setParameter('name', '%' . mb_strtolower($name) . '%')
            ;
        }

        if ($teacher) {
            $groups
                ->andWhere('g.teacher = :teacher')
                ->setParameter('teacher', $teacher)
            ;
        }

        $groups
            ->andWhere('g.isCompleted = :completed')
            ->setParameter('completed', false)
            ->orderBy('g.createdAt', 'DESC')
        ;

        return $groups;
    }
}