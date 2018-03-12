<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Course;
use Doctrine\ORM\EntityRepository;

class CourseRepository extends EntityRepository
{
    /**
     * @param string|null $current_date
     * @param string $operation
     * @param bool $delete
     * @param bool $collection
     * @param string $sort
     * @param string $order
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getListCoursesGroups(
        string $current_date = null,
        string $operation = '',
        bool $delete = false,
        bool $collection = false,
        string $sort = 'createdAt',
        string $order = 'ASC'
    )
    {
        $courses = $this
            ->_em
            ->getRepository(Course::class)
            ->createQueryBuilder('c')
            ->innerJoin('c.group', 'g');

        if($delete) {
            $courses = $courses
                ->where('g.deletedAt is not null');
        } elseif ($operation) {
            $courses = $courses
                ->where('g.createdAt' . $operation . ' :date')
                ->setParameters([
                    'date' => $current_date
                ]);
        }

        $courses = $courses
            ->addOrderBy('c.' . $sort, $order);

        if ($collection) {
            $courses = $courses
                ->getQuery()
                ->getResult();
        }

        return $courses;
    }
}