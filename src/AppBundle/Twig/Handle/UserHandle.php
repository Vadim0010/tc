<?php

namespace AppBundle\Twig\Handle;

use AppBundle\Entity\Groups;
use AppBundle\Entity\Lessons;
use AppBundle\Entity\Users;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

trait UserHandle
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getUserData(Users $user, Groups $group, Lessons $current_lesson)
    {
        return $user
            ->getLessonsList()
            ->filter( function ($lesson) use ($group, $current_lesson) {
                    return $lesson->getGroup() === $group
                        && $lesson->getLesson() === $current_lesson;
                    }
                )
            ->first()
        ;
    }

    public function getGroupsForChild(Users $child)
    {
        $paginator = $this->container->get('knp_paginator');
        $em = $this->container->get('doctrine.orm.entity_manager');
        $request = Request::createFromGlobals();

        return $paginator->paginate(
            $em->getRepository(Groups::class)->getAllGroupsForUser('listener', $child),
            $request->query->getInt($child->getId() . 'child_groups', 1),
            25,
            ['pageParameterName' => $child->getId() . 'child_groups']
        );
    }
}