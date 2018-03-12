<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Groups;
use AppBundle\Entity\Users;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ChildController extends Controller
{
    /**
     * @Route("/student/{child}/show",
     *     name="child-show",
     *     requirements={"child": "\d+"}
     * )
     *
     * Показать список детей для родителя
     *
     * @param Users $child
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showListChildrenToParentAction(Users $child, Request $request)
    {
        if (! $this->isGranted('ROLE_PARENT') || $this->getUser() !== $child->getParent()) {
            throw new AccessDeniedException('У Вас нет прав для просмотра данной страницы!');
        }

        $paginator = $this->get('knp_paginator');
        $sql_groups = $this
            ->getDoctrine()
            ->getRepository(Groups::class)
            ->getAllGroupsForUser('listener',$child, null, '', false, false, 'createdAt', 'DESC');

        $groups = $paginator->paginate(
            $sql_groups,
            $request->query->getInt('child_groups', 1),
            25,
            ['pageParameterName' => 'child_groups']
        );

        return $this->render('SonataAdminBundle:content:child_group_show.html.twig', ['child' => $child, 'groups' => $groups]);
    }

    /**
     * @Route("/student/group/{child}/{group}",
     *     name="child-group-show",
     *     requirements={"child": "\d+"},
     *     requirements={"group": "\d+"}
     * )
     *
     * Показать группу ребенка родителю
     *
     * @param Users $child
     * @param Groups $group
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showGroupChildToParentAction(Users $child, Groups $group)
    {
        if (! $this->isGranted('ROLE_PARENT') || $this->getUser() !== $child->getParent()) {
            throw new AccessDeniedException('У Вас нет прав для просмотра данной страницы!');
        }

        return $this
            ->render(
                'SonataAdminBundle:content:listener_group_show.html.twig',
                [
                    'group' => $group,
                    'listener' => $child
                ]
            )
        ;
    }
}
