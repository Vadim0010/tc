<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Users;
use AppBundle\Entity\Groups;
use AppBundle\Form\SearchGroupFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\PropertyAccess\PropertyAccess;

class HomeController extends Controller
{

    /**
     * @var false|string
     */
    private $current_date;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * Количество записей
     *
     * @var int
     */
    private $per_page = 25;

    /**
     * Количество записей при поиске
     *
     * @var int
     */
    private $search_groups_per_page = 5;

    public function __construct(EntityManagerInterface $em)
    {
        $this->current_date = new \DateTime();
        $this->em = $em;
    }

    /**
     * @Route("/", name="home")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        if ($this->isGranted(['ROLE_TEACHER', 'ROLE_CURATOR', 'ROLE_ADMIN', 'ROLE_CHILD', 'ROLE_PARENT', 'ROLE_LISTENER'])) {

            $accessor = PropertyAccess::createPropertyAccessor();
            $user = $this->getUser();
            $groups = $searchGroups = $users =  $children = $child = $child_id = null;
            $searchGroupForm = $this->createForm(SearchGroupFormType::class);
            $searchGroupForm->handleRequest($request);
            $logger = $this->get('logger');

            try {
                if ($searchGroupForm->isSubmitted() && $searchGroupForm->isValid()) {
                    $paginator = $this->get('knp_paginator');
                    $searchGroupData = $searchGroupForm->getData();

                    $searchGroups = $paginator->paginate(
                        $this->em->getRepository(Groups::class)->searchDqlGroups($searchGroupData),
                        $request->query->getInt('search_groups_page', 1),
                        $this->search_groups_per_page,
                        ['pageParameterName' => 'search_groups_page']
                    );
                }

                if (in_array($request->query->get('select'), ['list_teachers', 'list_curators']) and $this->isGranted(['ROLE_ADMIN', 'ROLE_CURATOR'])) {

                        if ($request->query->get('select') === 'list_teachers') {
                            $users_role = 'ROLE_TEACHER';
                        } elseif ($request->query->get('select') === 'list_curators') {
                            $users_role = 'ROLE_CURATOR';
                        } else {
                            $users_role = null;
                        }

                    if ($users_role) {
                        $users = $this->getAllUsersDependenceRole($users_role, false, $request->query->getInt('page', 1));
                    }

                } elseif (preg_match('/^child_(\d+)$/', $request->query->get('select'), $data)) {

                    $child_id = $accessor->getValue($data, '[1]');

                } else {

                    if ($this->isGranted(['ROLE_ADMIN', 'ROLE_TEACHER', 'ROLE_CURATOR', 'ROLE_LISTENER'])) {
                        $groups = $this->getGroupsDependenceRole($user, $request);
                    }

                }

                if ($this->isGranted('ROLE_PARENT')) {
                    $children = $this->em->getRepository(Users::class)->findBy(['parent' => $user]);

                    if ($child_id) {
                        $child = is_numeric($child_id) ? $this->em->getRepository(Users::class)->findOneBy(['id' => $child_id, 'parent' => $user]) : null;
                        if (! $child) {
                            throw $this->createAccessDeniedException(
                                sprintf('Could not get child (child_id = %s) for current user (user_id = %s). Error in the function %s on line %s',
                                    $child_id ?? 'child_id not found',
                                    $this->getUser() ? $this->getUser()->getId() : 'user_id not found',
                                    __METHOD__,
                                    __LINE__
                                )
                            );
                        }
                    } elseif (! $groups) {
                        $child = $accessor->getValue($children, '[0]');
                    }
                }

                return $this->render('SonataAdminBundle::home_layout.html.twig', [
                    'searchGroupForm' => $searchGroupForm->createView(),
                    'users' => $users,
                    'groups' => $groups,
                    'searchGroups' => $searchGroups,
                    'children' => $children,
                    'child' => $child
                ]);
            } catch (\Exception $e) {
                $logger->error($e->getMessage());
                throw $this->createNotFoundException();
            }
        }


        return $this->redirectToRoute('sonata_admin_dashboard');
    }

    /**
     * Получить список групп взависимости от роли
     *
     * @param $user
     * @param $request
     * @return array
     */
    private function getGroupsDependenceRole($user, $request)
    {
        $date = $this->current_date->add(new \DateInterval('P1D'))->format('Y.m.d H:i:s');
        $paginator = $this->get('knp_paginator');
        $completed = false;

        if ($request->query->has('select')) {
            $type_groups = $this->getTypeGroups($request->query->get('select'));
            if ($request->query->get('select') === 'completed_groups') {
                $date = null;
                $completed = true;
            }
        } else {
            $type_groups = '<=';
        }

        if ($request->query->has('role')) {
            $role = $this->getRole($request->query->get('role'));
        } else {
            $role = $this->getDefaultRole();
        }

        $groups = $paginator->paginate(
            $this->em->getRepository(Groups::class)->getAllGroupsForUser( $role, $user, $date, $type_groups, $completed),
            $request->query->getInt('page', 1),
            $this->per_page
        );

        return $groups;
    }

    /**
     * Получить список преподавателей/кураторов
     *
     * @param $role
     * @param $collection
     * @param $page
     * @return \Knp\Component\Pager\Pagination\PaginationInterface
     */
    private function getAllUsersDependenceRole($role, $collection, $page)
    {
        $paginator = $this->get('knp_paginator');

        return $paginator->paginate(
            $this->em->getRepository(Users::class)->getAllUsersDependenceRole($role, $collection),
            $page,
            $this->per_page
        );

    }

    /**
     * Получить тип групп (текущие, будущие или завершенные)
     *
     * @param $type
     * @return string
     */
    private function getTypeGroups($type)
    {
        switch ($type) {
            case 'active_groups':
                return '<=';
            case 'future_groups':
                return '>';
            case 'completed_groups':
                return '';
            default:
                return '<=';
        }
    }

    /**
     * @param $role
     * @return string
     */
    private function getRole($role)
    {
        if ($role === 'admin' && $this->isGranted('ROLE_ADMIN')) {
            return 'admin';
        } elseif ($role === 'curator' && $this->isGranted('ROLE_CURATOR')) {
            return 'curator';
        } elseif ($role === 'teacher' && $this->isGranted('ROLE_TEACHER')) {
            return 'teacher';
        }

        return $this->getDefaultRole();
    }

    /**
     * @return string
     */
    private function getDefaultRole()
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return 'admin';
        } elseif ($this->isGranted('ROLE_TEACHER')) {
            return 'teacher';
        } elseif ($this->isGranted('ROLE_CURATOR')) {
            return 'curator';
        }

        return 'listener';
    }
}