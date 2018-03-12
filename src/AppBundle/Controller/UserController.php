<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Groups;
use AppBundle\Entity\Users;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Exception\LockException;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;

class UserController extends CRUDController
{
    public function createAction()
    {
        $request = $this->getRequest();
        $templateKey = 'edit';

        $userManager = $this->get('fos_user.user_manager');
        $dispatcher = $this->get('event_dispatcher');

        $this->admin->checkAccess('create');

        $user = $userManager->createUser();
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $this->admin->setSubject($user);

        $form = $this->admin->getForm();
        $form->setData($user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if (method_exists($this->admin, 'preValidate')) {
                $this->admin->preValidate($user);
            }

            $isFormValid = $form->isValid();

            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
                try{
                    $event = new FormEvent($form, $request);
                    $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);
                    $children = $this->getChildren($user);
                    list($roles, $groups, $teacherGroups, $curatorGroups, $parent) = $this->getFormData($form);

                    $handle_roles = $this->handleRolesToUser(
                        'create',
                        $roles, [
                            'ROLE_LISTENER' => $groups,
                            'ROLE_TEACHER' => $teacherGroups,
                            'ROLE_CURATOR' => $curatorGroups,
                            'ROLE_CHILD' => $parent,
                            'ROLE_PARENT' => $children
                        ]
                    );

                    $user->setRoles($handle_roles);
                    $userManager->updateUser($user);

                    if ($this->isXmlHttpRequest()) {
                        return $this->renderJson(array(
                            'result' => 'ok',
                            'objectId' => $this->admin->getNormalizedIdentifier($user),
                        ), 200, array());
                    }

                    $this->addFlash(
                        'sonata_flash_success',
                        $this->trans(
                            'flash_create_success',
                            array('%name%' => $this->escapeHtml($this->admin->toString($user))),
                            'SonataAdminBundle'
                        )
                    );

                    // redirect to edit mode
                    return $this->redirectTo($user);
                } catch (ModelManagerException $e) {
                    $this->handleModelManagerException($e);

                    $isFormValid = false;
                }
            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if (!$this->isXmlHttpRequest()) {
                    $this->addFlash(
                        'sonata_flash_error',
                        $this->trans(
                            'flash_create_error',
                            array('%name%' => $this->escapeHtml($this->admin->toString($user))),
                            'SonataAdminBundle'
                        )
                    );
                }
            } elseif ($this->isPreviewRequested()) {
                // pick the preview template if the form was valid and preview was requested
                $templateKey = 'preview';
                $this->admin->getShow();
            }
        }

        $formView = $form->createView();
        $this->setFormTheme($formView, $this->admin->getFormTheme());

        return $this->render($this->admin->getTemplate($templateKey), array(
            'action' => 'create',
            'form' => $formView,
            'object' => $user,
        ), null);
    }

    public function editAction($id = null)
    {
        $request = $this->getRequest();
        $templateKey = 'edit';

        $id = $request->get($this->admin->getIdParameter());
        $user = $this->admin->getObject($id);

        if (!$user) {
            throw $this->createNotFoundException(sprintf('Не удалось найти пользователя с идентификатором: %s', $id));
        }

        $dispatcher = $this->get('event_dispatcher');
        $this->admin->checkAccess('edit', $user);

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $preResponse = $this->preEdit($request, $user);
        if ($preResponse !== null) {
            return $preResponse;
        }

        $this->admin->setSubject($user);

        $form = $this->admin->getForm();
        $form->setData($user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if (method_exists($this->admin, 'preValidate')) {
                $this->admin->preValidate($user);
            }

            $isFormValid = $form->isValid();

            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
                try {
                    $userManager = $this->get('fos_user.user_manager');
                    $event = new FormEvent($form, $request);
                    $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_SUCCESS, $event);
                    $children = $this->getChildren($user);
                    list($roles, $groups, $teacherGroups, $curatorGroups, $parent) = $this->getFormData($form);

                    $handle_roles = $this->handleRolesToUser(
                        'edit',
                        $roles, [
                            'ROLE_LISTENER' => $groups,
                            'ROLE_TEACHER' => $teacherGroups,
                            'ROLE_CURATOR' => $curatorGroups,
                            'ROLE_CHILD' => $parent,
                            'ROLE_PARENT' => $children
                        ]
                    );

                    $user->setRoles($handle_roles);
                    $userManager->updateUser($user);

                    if ($this->isXmlHttpRequest()) {
                        return $this->renderJson(array(
                            'result' => 'ok',
                            'objectId' => $this->admin->getNormalizedIdentifier($user),
                            'objectName' => $this->escapeHtml($this->admin->toString($user)),
                        ), 200, array());
                    }

                    $this->addFlash(
                        'sonata_flash_success',
                        $this->trans(
                            'flash_edit_success',
                            array('%name%' => $this->escapeHtml($this->admin->toString($user))),
                            'SonataAdminBundle'
                        )
                    );

                    // redirect to edit mode
                    return $this->redirectTo($user);
                } catch (ModelManagerException $e) {
                    $this->handleModelManagerException($e);

                    $isFormValid = false;
                } catch (LockException $e) {
                    $this->addFlash('sonata_flash_error', $this->trans('flash_lock_error', array(
                        '%name%' => $this->escapeHtml($this->admin->toString($user)),
                        '%link_start%' => '<a href="'.$this->admin->generateObjectUrl('edit', $user).'">',
                        '%link_end%' => '</a>',
                    ), 'SonataAdminBundle'));
                }
            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if (!$this->isXmlHttpRequest()) {
                    $this->addFlash(
                        'sonata_flash_error',
                        $this->trans(
                            'flash_edit_error',
                            array('%name%' => $this->escapeHtml($this->admin->toString($user))),
                            'SonataAdminBundle'
                        )
                    );
                }
            } elseif ($this->isPreviewRequested()) {
                // enable the preview template if the form was valid and preview was requested
                $templateKey = 'preview';
                $this->admin->getShow();
            }
        }

        $formView = $form->createView();
        // set the theme for the current Admin Form
        $this->setFormTheme($formView, $this->admin->getFormTheme());

        return $this->render($this->admin->getTemplate($templateKey), array(
            'action' => 'edit',
            'form' => $formView,
            'object' => $user,
        ), null);
    }

    public function deleteAction($id)
    {
        $request = $this->getRequest();
        $id = $request->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw $this->createNotFoundException('Не удалось найти данного пользователя');
        }

        $this->admin->checkAccess('delete', $object);

        $preResponse = $this->preDelete($request, $object);
        if ($preResponse !== null) {
            return $preResponse;
        }

        if ($this->getRestMethod() == 'DELETE') {
            // check the csrf token
            $this->validateCsrfToken('sonata.delete');

            $objectName = $this->admin->toString($object);

            try {

                $this->removeTeacherOrCuratorFromGroups($object, 'teacher');
                $this->removeTeacherOrCuratorFromGroups($object, 'curator');
                $this->removeParentFromListener($object);

                $this->admin->delete($object);

                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(array('result' => 'ok'), 200, array());
                }

                $this->addFlash(
                    'sonata_flash_success',
                    $this->trans(
                        'flash_delete_success',
                        array('%name%' => $this->escapeHtml($objectName)),
                        'SonataAdminBundle'
                    )
                );
            } catch (ModelManagerException $e) {
                $this->handleModelManagerException($e);

                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(array('result' => 'error'), 200, array());
                }

                $this->addFlash(
                    'sonata_flash_error',
                    $this->trans(
                        'flash_delete_error',
                        array('%name%' => $this->escapeHtml($objectName)),
                        'SonataAdminBundle'
                    )
                );
            }

            return $this->redirectTo($object);
        }

        return $this->render($this->admin->getTemplate('delete'), array(
            'object' => $object,
            'action' => 'delete',
            'csrf_token' => $this->getCsrfToken('sonata.delete'),
        ), null);
    }

    protected function preShow(Request $request, $object)
    {
        if($object instanceof Users) {
            $groups = [
                'teacher' => [],
                'curator' => [],
                'listener' => []
            ];
            $paginator = $this->get('knp_paginator');

            if (in_array('ROLE_TEACHER', $object->getRoles())) {
                $groups['teacher'] = $paginator->paginate(
                    $this->getGroups($object, 'teacher'),
                    $request->query->getInt('t_groups', 1),
                    25,
                    ['pageParameterName' => 't_groups']
                );
            }

            if (in_array('ROLE_CURATOR', $object->getRoles())) {
                $groups['curator'] = $paginator->paginate(
                    $this->getGroups($object, 'curator'),
                    $request->query->getInt('c_groups', 1),
                    25,
                    ['pageParameterName' => 'c_groups']
                );
            }

            if ( in_array('ROLE_LISTENER', $object->getRoles()) ) {
                $groups['listener'] = $paginator->paginate(
                    $object->getUserGroups(),
                    $request->query->getInt('l_groups', 1),
                    25,
                    ['pageParameterName' => 'l_groups']
                );
            }

            $object->children = $this->getChildren($object);
            $object->list_groups = $groups;
        }
    }

    /**
     * Установить шаблон
     *
     * @param FormView $formView
     * @param string $theme
     */
    private function setFormTheme(FormView $formView, $theme)
    {
        $twig = $this->get('twig');

        try {
            $twig
                ->getRuntime('Symfony\Bridge\Twig\Form\TwigRenderer')
                ->setTheme($formView, $theme);
        } catch (\Twig_Error_Runtime $e) {
            // BC for Symfony < 3.2 where this runtime not exists
            $twig
                ->getExtension('Symfony\Bridge\Twig\Extension\FormExtension')
                ->renderer
                ->setTheme($formView, $theme);
        }
    }

    /**
     * Получить данные из формы
     *
     * @param $form
     * @return array
     */
    private function getFormData($form) :array
    {
        return [
            $form->get('roles')->getData(),
            $form->get('user_groups')->getData(),
            $form->get('teacherGroup')->getData(),
            $form->get('curatorGroup')->getData(),
            $form->get('parent')->getData()
        ];
    }

    /**
     * Обработать роли при добавлении и редактировании пользователя
     *
     * @param string $action
     * @param array $roles
     * @param array $handle_roles
     * @return array $roles
     */
    private function handleRolesToUser(string $action, array $roles, array $handle_roles) :array
    {
        if ($action === 'create') {
            foreach ($handle_roles as $key => $arg) {
                $roles = $this->setRolesUser($key, $roles, $arg);
            }
        } elseif ($action === 'edit') {
            foreach ($handle_roles as $key => $arg) {
                $roles = $this->setRolesUser($key, $roles, $arg);
            }
        }

        if (in_array('ROLE_CHILD', $roles)) {
            if (! in_array('ROLE_LISTENER', $roles)) {
                array_push($roles, 'ROLE_LISTENER');
            }
        }

        return $roles;
    }

    /**
     * Добавить роль при создании/редактировании пользователя
     *
     * @param string $check_role
     * @param array $roles
     * @param $object
     * @return array $roles
     */
    private function setRolesUser(string $check_role, array $roles, $object) :array
    {
        if (count($object)) {
            if ( ! in_array($check_role, $roles) ) {
                array_push($roles, $check_role);
            }
        }

        return $roles;
    }

    /**
     * Получить для пользователя всех детей
     *
     * @param $user
     * @return Users[]|array
     */
    private function getChildren($user)
    {
        return $this
            ->getDoctrine()
            ->getManager()
            ->getRepository(Users::class)
            ->findBy(['parent' => $user]);
    }

    /**
     * При удалении преподавателя/куратора удалить данные у групп
     *
     * @param $user
     * @param $role
     */
    private function removeTeacherOrCuratorFromGroups($user, $role)
    {
        $groups = $this->getGroups($user, $role);

        if (count($groups)) {
            if ($role == 'teacher') {
                foreach ($groups as $group) {
                    $group->setTeacher();
                }
            } elseif ($role == 'curator') {
                foreach ($groups as $group) {
                    $group->setCurator();
                }
            }
        }
    }

    /**
     * Получить список групп
     *
     * @param $user
     * @param $role
     * @param string $sort
     * @param string $order
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getGroups($user, $role, $sort = 'createdAt', $order = 'DESC')
    {
        $em = $this->getDoctrine()->getManager();
        return $em
            ->getRepository(Groups::class)
            ->getAllGroupsForUser($role, $user, null, '', false, true, $sort, $order);
    }

    /**
     * При удалении родителя, найти и удалить его у всех детей
     *
     * @param $user
     */
    private function removeParentFromListener($user)
    {
        $em = $this->getDoctrine()->getManager();
        $children = $em->getRepository(Users::class)->findBy(['parent' => $user]);

        if  (count($children)){
            foreach ($children as $child) {
                $child->setParent();
            }
        }
    }
}