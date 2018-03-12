<?php

namespace AppBundle\Admin;

use AppBundle\Classes\Admin;
use AppBundle\Entity\Groups;
use AppBundle\Entity\Users;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UserAdmin extends Admin
{
    protected $searchResultActions = ['show'];

    protected function configureFormFields(FormMapper $form)
    {
        if (
            $this->getSubject() instanceof Users
            && in_array('ROLE_SUPER_ADMIN', $this->getSubject()->getRoles())
            && ! $this->isGranted('ROLE_SUPER_ADMIN')
        ) {
            throw new AccessDeniedException();
        }

        $request = $this->getRequest()->query;
        $groups = $this->getGroupsNotCompleted();

        if ( $request->has('listener') ) {
            if ( $request->get('listener') == 'new_group' ) {
                $form
                    ->tab('Группы')
                        ->with('Добавить слушателем в группу')
                            ->add('user_groups', 'sonata_type_model', [
                                'label' => 'Список групп',
                                'property' => 'groupName',
                                'required' => false,
                                'multiple' => true,
                                'by_reference' => false,
                                'btn_add' => 'Создать новую группу',
                                'query' => $groups
                            ])
                        ->end()
                    ->end()
                ;
            }
        }

        $form
            ->tab('Пользователь')
                ->with('Добавить пользователя', ['class' => 'col-md-7 col-lg-7'])
                    ->add('last_name', 'text', [
                        'label' => 'Фамилия',
                        'required' => true
                    ])
                    ->add('first_name', 'text', [
                        'label' => 'Имя',
                        'required' => true
                    ])
                    ->add('middle_name', 'text', [
                        'label' => 'Отчество',
                        'required' => false
                    ])
                    ->add('phone', 'text', [
                        'label' => 'Телефон',
                        'required' => true
                    ])
                    ->add('address', 'text', [
                        'label' => 'Адрес',
                        'required' => false
                    ])
                    ->add('email', EmailType::class, [
                        'label' => 'E-Mail'
                    ])
        ;

        if( $this->request->get('_route') != 'admin_app_users_edit' ){
            $form
                ->add('plainPassword', RepeatedType::class, [
                    'type' => 'text',
                    'options' => ['translation_domain' => 'FOSUserBundle'],
                    'first_options' => ['label' => 'form.password'],
                    'second_options' => ['label' => 'form.password_confirmation'],
                    'invalid_message' => 'fos_user.password.mismatch',
                ])
            ;
        }

        $form
                ->end()
                ->with('Добавить роль', ['class' => 'col-md-5 col-lg-5'])
                    ->add('roles', 'choice', [
                        'label' => 'Роли:',
                        'choices' => [
                            'Слушатель' => 'ROLE_LISTENER',
                            'Родитель' => 'ROLE_PARENT',
                            'Ребенок'   => 'ROLE_CHILD',
                            'Преподаватель' => 'ROLE_TEACHER',
                            'Куратор' => 'ROLE_CURATOR',
                            'Бухгалтер' => 'ROLE_ACCOUNTANT',
                            'Администратор' => 'ROLE_ADMIN'
                        ],
                        'multiple' => true
                    ])
                ->end()
                ->with('Назначить куратором', ['class' => 'col-md-5 col-lg-5'])
                    ->add('curatorGroup', 'sonata_type_model', [
                        'label' => 'Добавить куратором в группу',
                        'property' => 'groupName',
                        'multiple' => true,
                        'by_reference' => false,
                        'required' => false,
                        'btn_add' => 'Создать новую группу',
                        'query' => $groups
                    ])
                ->end()
                ->with('Назначить преподавателем', ['class' => 'col-md-5 col-lg-5'])
                    ->add('teacherGroup', 'sonata_type_model', [
                        'label' => 'Добавить преподавателем в группу',
                        'property' => 'groupName',
                        'multiple' => true,
                        'by_reference' => false,
                        'required' => false,
                        'btn_add' => 'Создать новую группу',
                        'query' => $groups
                    ])
                ->end()
            ->end()
        ;

        if ( ! $request->has('listener') ) {
            $form
                ->tab('Группы')
                    ->with('Добавить слушателем в группу')
                        ->add('user_groups', 'sonata_type_model', [
                            'label' => 'Список групп',
                            'property' => 'groupName',
                            'required' => false,
                            'multiple' => true,
                            'by_reference' => false,
                            'btn_add' => 'Создать новую группу',
                            'query' => $groups
                        ])
                    ->end()
                ->end()
            ;
        }

        $form
            ->tab('Родители')
                ->with('Добавить родителя')
                    ->add('parent', 'sonata_type_model', [
                        'label' => 'Список родителей',
                        'property' => 'fullName',
                        'required' => false,
                        'query' => $this->getUsers('ROLE_PARENT'),
                        'btn_add' => 'Добавить нового родителя'
                    ])
                ->end()
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('email', null, [
                'label' => 'E-Mail'
            ])
            ->add('last_name', null, [
                'label' => 'Фамилия'
            ])
            ->add('first_name', null, [
                'label' => 'Имя'
            ])
            ->add('middle_name', null, [
                'label' => 'Отчество'
            ])
            ->add('phone', null, [
                'label' => 'Телефон'
            ])
            ->add('user_groups', null, ['label' => 'Группа'], 'entity', [
                'class' => 'AppBundle\Entity\Groups',
                'choice_label' => 'groupName',
            ])
            ->add('roles', null, ['label' => 'Роль'], 'choice', [
                'choices' => [
                    'Администратор' => 'ROLE_ADMIN',
                    'Бухгалтер' => 'ROLE_ACCOUNTANT',
                    'Слушатель' => 'ROLE_LISTENER',
                    'Родитель' => 'ROLE_PARENT',
                    'Ребенок'   => 'ROLE_CHILD',
                    'Преподаватель' => 'ROLE_TEACHER',
                    'Куратор' => 'ROLE_CURATOR'
                ]
            ])
        ;
    }

    protected function configureListFields(ListMapper $list)
    {
        $list
            ->add('id', null, [
                'label' => 'ID'
            ])
            ->addIdentifier('email', null, [
                'label' => 'E-Mail',
                'route' => [
                    'name' => 'show'
                ]
            ])
            ->add('last_name', null, [
                'label' => 'Фамилия'
            ])
            ->add('first_name', null, [
                'label' => 'Имя'
            ])
            ->add('middle_name', null, [
                'label' => 'Отчество'
            ])
            ->add('phone', null, [
                'label' => 'Телефон'
            ])
            ->add('roles', 'choice', [
                'label' => 'Роль',
                'multiple' => true,
                'delimiter' => ' ',
                'template' => 'SonataAdminBundle:CRUD:user_base_list_roles.html.twig',
                'choices' => [
                    'ROLE_ADMIN' => 'Администратор',
                    'ROLE_SUPER_ADMIN' => 'Администратор',
                    'ROLE_ACCOUNTANT' => 'Бухгалтер',
                    'ROLE_LISTENER' => 'Слушатель',
                    'ROLE_PARENT' => 'Родитель',
                    'ROLE_CHILD' => 'Ребенок',
                    'ROLE_TEACHER' => 'Преподаватель',
                    'ROLE_CURATOR' => 'Куратор',
                ]
            ])
            ->add('_action', null, [
                'label' => 'Действия',
                'actions' => [
                    'edit' => [],
                    'delete' => []
                ]
            ])
//            ->add('user_groups', null, [
//                'label' => 'Группа',
//                'associated_property' => 'groupName',
//                'template' => 'SonataAdminBundle:CRUD:user_base_list_group.html.twig',
//                'route' => [
//                    'name' => 'show'
//                ]
//            ])
        ;
    }

    protected function configureShowFields(ShowMapper $show)
    {
        $user = $this->getSubject();

        $show
            ->tab('Профиль')
                ->with('Профиль пользователя')
                    ->add('last_name', null, [
                        'label' => 'Фамилия'
                    ])
                    ->add('first_name', null , [
                        'label' => 'Имя'
                    ])
                    ->add('middle_name', null , [
                        'label' => 'Отчество'
                    ])
                    ->add('phone', null , [
                        'label' => 'Телефон'
                    ])
                    ->add('address', null , [
                        'label' => 'Адрес'
                    ])
                    ->add('email', null, [
                        'label' => 'E-mail'
                    ])
                    ->add('roles', null, [
                        'label' => 'Роль',
                    ])
                    ->add('createdAt', null, [
                        'label' => 'Дата регистрации',
                        'format' => 'd.m.Y'
                    ])
                ->end()
            ->end()
        ;

        if ( property_exists($user, 'list_groups') ) {
            if ( in_array('ROLE_TEACHER', $user->getRoles()) ) {
                $show
                    ->tab('Данные преподавателя')
                        ->with('')
                            ->add('teacherGroup')
                        ->end()
                    ->end()
                ;
            }

            if ( in_array('ROLE_CURATOR', $user->getRoles()) ) {
                $show
                    ->tab('Данные куратора')
                        ->with('')
                            ->add('curatorGroup')
                        ->end()
                    ->end()
                ;
            }
        }

        if ( in_array('ROLE_LISTENER', $user->getRoles()) ) {
            $show
                ->tab('Группы')
                    ->with('')
                        ->add('user_groups')
                    ->end()
                ->end()
            ;
        }

        if ( count($user->getParent()) ) {
            $show
                ->tab('Родители')
                    ->with('')
                        ->add('parent', null, [
                            'route' => [
                                'name' => 'show'
                            ]
                        ])
                    ->end()
                ->end()
            ;
        }
        if ( property_exists($user, 'children') ) {
            if (count($user->children)) {
                $show
                    ->tab('Дети')
                        ->with('')
                            ->add('child')
                        ->end()
                    ->end()
                ;
            }
        }
    }

    protected function getUsers($role)
    {
        return $this
            ->getModelManager()
            ->getEntityManager(Users::class)
            ->createQueryBuilder()
            ->select('u')
            ->from(Users::class, 'u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%' . $role . '%')
        ;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        unset($actions['delete']);

        return $actions;
    }

    public function toString($user)
    {
        return $user instanceof Users ? $user->getFullName() : 'Не удалось найти пользователя';
    }

    private function getGroupsNotCompleted()
    {
        return $this
            ->getModelManager()
            ->getEntityManager(Groups::class)
            ->createQueryBuilder()
            ->select('g')
            ->from(Groups::class, 'g')
            ->where('g.isCompleted != :isCompleted')
            ->setParameter('isCompleted', true)
        ;
    }
}