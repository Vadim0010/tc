<?php

namespace AppBundle\Admin;

use AppBundle\Classes\Admin;
use AppBundle\Entity\Course;
use AppBundle\Entity\Groups;
use AppBundle\Entity\Users;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class GroupAdmin extends Admin
{
    protected $searchResultActions = ['show'];

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('course', null, ['label' => 'Курс'], 'entity', [
                'class'    => Course::class,
                'choice_label' => 'title',
            ])
            ->add('number', null, ['label' => 'Номер группы'], null, [])
            ->add('title', null, ['label' => 'Название группы'], null, [])
            ->add('teacher', null, ['label' => 'Преподаватель'], 'entity', [
                'class'    => Users::class,
                'choice_label' => 'fullName',
                'query_builder' => $this->getUsers('ROLE_TEACHER'),
            ])
            ->add('curator', null, ['label' => 'Куратор'], 'entity', [
                'class'    => Users::class,
                'choice_label' => 'fullName',
                'query_builder' => $this->getUsers('ROLE_CURATOR')
            ])
            ->add('daysLessons', null, [
                'label' => 'Дни занятий'
            ], 'choice', [
                'choices' => [
                    'Пн' => 1,
                    'Вт' => 2,
                    'Ср' => 3,
                    'Чт' => 4,
                    'Пт' => 5,
                    'Сб' => 6,
                    'Вс' => 7
                ]
            ])
            ->add('createdAt', 'doctrine_orm_date', [
                'label' => 'Дата начала'
            ], 'sonata_type_date_picker', [
                'format' => 'dd.MM.yyyy'
            ])
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('course', 'entity', [
                'label' => 'Курс',
                'associated_property' => 'courseName',
                'route' => [
                    'name' => 'show'
                ]
            ])
            ->addIdentifier('number', null, [
                'label' => 'Номер группы',
                'route' => [
                    'name' => 'show'
                ]
            ])
            ->addIdentifier('title', null, [
                'label' => 'Название группы',
                'route' => [
                    'name' => 'show'
                ]
            ])
            ->add('createdAt', 'datetime', [
                'label' => 'Дата начала',
                'format' => 'd.m.Y H:i'
            ])
            ->add('daysLessons', 'choice', [
                'label' => 'Дни занятий',
                'multiple' => true,
                'choices' => [
                    1 => 'Пн',
                    2 => 'Вт',
                    3 => 'Ср',
                    4 => 'Чт',
                    5 => 'Пт',
                    6 => 'Сб',
                    7 => 'Вс'
                ]
            ])
            ->add('teacher', 'entity', [
                'label' => 'Преподаватель',
                'associated_property' => 'fullName'
            ])
            ->add('curator', 'entity', [
                'label' => 'Куратор',
                'associated_property' => 'fullName'
            ])
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $request = $this->getRequest()->query;
        $course_id = $request->get('course');
        $teacher_id = $request->get('teacher');
        $curator_id = $request->get('curator');
        $course = $course_id ? $this->getCourse($course_id) : null;

        if ($request->has('change_teacher') || $request->has('change_curator')) {

            if ($request->get('change_teacher') == 'edit' || $request->get('change_curator') == 'edit') {
                $formMapper
                    ->tab('Заменить')
                ;
            } elseif ($request->get('change_teacher') == 'new' || $request->get('change_curator') == 'new') {
                $formMapper
                    ->tab('Добавить')
                ;
            }

            if ($request->get('change_teacher') == 'edit') {
                $formMapper
                    ->with(
                        sprintf('Заменить преподавателя в группе № %s - %s',
                            $this->getSubject()->getNumber(),
                            $this->getSubject()->getTitle()
                        )
                    )
                        ->add('teacher', 'sonata_type_model', [
                            'class' => Users::class,
                            'property' => 'fullName',
                            'label' => 'Список преподавателей',
                            'query' => $this->getUsers('ROLE_TEACHER'),
                            'required' => false,
                            'btn_add' => 'Добавить нового преподавателя'
                        ])
                ;
            } elseif ($request->get('change_teacher') == 'new') {
                $formMapper
                    ->with(
                        sprintf('Добавить нового преподавателя в группу № %s - %s',
                            $this->getSubject()->getNumber(),
                            $this->getSubject()->getTitle()
                        )
                    )
                        ->add('teacher', 'sonata_type_model', [
                            'class' => Users::class,
                            'property' => 'fullName',
                            'label' => 'Список преподавателей',
                            'query' => $this->getUsers('ROLE_TEACHER'),
                            'required' => false,
                            'data' => null,
                            'btn_add' => 'Добавить нового преподавателя'
                        ])
                ;
            } elseif ($request->get('change_curator') == 'edit') {
                $formMapper
                    ->with(
                        sprintf('Заменить куратора в группе № %s - %s',
                            $this->getSubject()->getNumber(),
                            $this->getSubject()->getTitle()
                        )
                    )
                        ->add('curator', 'sonata_type_model', [
                            'class' => Users::class,
                            'property' => 'fullName',
                            'label' => 'Список кураторов',
                            'query' => $this->getUsers('ROLE_CURATOR'),
                            'required' => false,
                            'btn_add' => 'Добавить нового куратора'
                        ])
                ;
            } elseif ($request->get('change_curator') == 'new') {
                $formMapper
                    ->with(
                        sprintf('Добавить нового куратора в группу № %s - %s',
                            $this->getSubject()->getNumber(),
                            $this->getSubject()->getTitle()
                        )
                    )
                        ->add('curator', 'sonata_type_model', [
                            'class' => Users::class,
                            'property' => 'fullName',
                            'label' => 'Список кураторов',
                            'query' => $this->getUsers('ROLE_CURATOR'),
                            'required' => false,
                            'data' => null,
                            'btn_add' => 'Добавить нового куратора'
                        ])
                ;
            }

            $formMapper
                    ->end()
                ->end()
            ;

        }

        if ( $request->has('group_listeners') ) {
            if ($request->get('group_listeners') == 'add_listener') {
                $formMapper
                    ->tab('Слушатели')
                        ->with('Добавить слушателей')
                            ->add('users', 'sonata_type_model', [
                                'label' => 'Список слушателей',
                                'class' => Users::class,
                                'multiple' => true,
                                'property' => 'fullName',
                                'query' => $this->getUsers('ROLE_LISTENER'),
                                'required' => false,
                                'btn_add' => 'Добавить нового слушателя'
                            ])
                        ->end()
                    ->end()
                ;
            }
        }

        $formMapper
            ->tab('Данные о группе')
        ;

        if ($course) {
            $formMapper
                ->with(
                    sprintf('Добавить новую группу для курса %s', $course->getQuery()->getSingleResult()->getCourseName()),
                    ['class'=>'col-md-7 col-lg-7']
                )
                    ->add('course', 'sonata_type_model', [
                        'label' => 'Название курса',
                        'class' => Course::class,
                        'property' => 'courseName',
                        'query' => $course,
                        'btn_add' => false
                    ])
            ;
        } else {

            if ($this->request->get('_route') == 'admin_app_groups_edit') {
                $formMapper
                    ->with('Редактировать группу', ['class'=>'col-md-7 col-lg-7'])
                ;
            } else {
                $formMapper
                    ->with('Добавить новую группу', ['class'=>'col-md-7 col-lg-7'])
                ;
            }

            $formMapper
                    ->add('course', 'sonata_type_model', [
                        'label' => 'Название курса',
                        'class' => Course::class,
                        'property' => 'courseName',
                        'btn_add' => 'Добавить новый курс'
                    ])
            ;
        }

        $formMapper
                ->add('number', null, [
                    'label' => 'Номер группы',
                    'required' => true
                ])
                ->add('title', null, [
                    'label' => 'Название группы'
                ])
                ->add('createdAt', 'sonata_type_datetime_picker', [
                    'label' => 'Дата начала',
                    'required' => true,
                    'format' => 'dd.MM.yyyy HH:mm',
                    'dp_side_by_side' => true,
                    'dp_collapse' => false,
                    'dp_use_seconds' => false
                ])
                ->add('daysLessons', 'choice', [
                    'label' => 'Дни занятий',
                    'multiple' => true,
                    'required' => false,
                    'expanded' => true,
                    'choices' => [
                        'Понедельник' => 1,
                        'Вторник' => 2,
                        'Среда' => 3,
                        'Четверг' => 4,
                        'Пятница' => 5,
                        'Суббота' => 6,
                        'Воскресенье' => 7
                    ]
                ])
        ;

        if  ($this->request->get('_route') != 'admin_app_groups_edit') {
            $formMapper
                ->add('isCompleted', 'hidden', [
                    'attr' => ['value' => 0],
                ])
            ;
        }

        $formMapper
            ->end();

        if ( ! $request->has('change_teacher') ) {
            $formMapper
                ->with('Назначить преподавателя', ['class' => 'col-md-5 col-lg-5']);

            if ($teacher_id) {
                $formMapper
                    ->add('teacher', 'sonata_type_model', [
                        'class' => Users::class,
                        'property' => 'fullName',
                        'label' => 'Преподаватель',
                        'query' => $this->getUsers(null, $teacher_id),
                        'btn_add' => false
                    ]);
            } else {
                $formMapper
                    ->add('teacher', 'sonata_type_model', [
                        'class' => Users::class,
                        'property' => 'fullName',
                        'label' => 'Список преподавателей',
                        'query' => $this->getUsers('ROLE_TEACHER'),
                        'required' => false,
                        'btn_add' => 'Добавить нового преподавателя'
                    ]);
            }

            $formMapper
                ->end();
        }

        if (! $request->has('change_curator')) {

            $formMapper
                ->with('Назначить куратора', ['class' => 'col-md-5 col-lg-5']);

            if ($curator_id) {
                $formMapper
                    ->add('curator', 'sonata_type_model', [
                        'class' => Users::class,
                        'property' => 'fullName',
                        'label' => 'Куратор',
                        'query' => $this->getUsers(null, $curator_id),
                        'btn_add' => false
                    ]);
            } else {
                $formMapper
                    ->add('curator', 'sonata_type_model', [
                        'class' => Users::class,
                        'property' => 'fullName',
                        'label' => 'Список кураторов',
                        'query' => $this->getUsers('ROLE_CURATOR'),
                        'required' => false,
                        'btn_add' => 'Добавить нового куратора'
                    ]);
            }

            $formMapper
                ->end()
            ;
        }

        if( $this->request->get('_route') == 'admin_app_groups_edit') {
            $formMapper
                ->with('Статус', ['class' => 'col-md-5 col-lg-5'])
                ->add('isCompleted', null, [
                    'label' => '"Завершено"'
                ])
                ->end();
        }

        $formMapper
            ->end()
        ;

        if ( ! $request->has('group_listeners') ) {
            $formMapper
                ->tab('Слушатели')
                    ->with('Добавить слушателей')
                        ->add('users', 'sonata_type_model', [
                            'label' => 'Список слушателей',
                            'class' => Users::class,
                            'multiple' => true,
                            'property' => 'fullName',
                            'query' => $this->getUsers('ROLE_LISTENER'),
                            'required' => false,
                            'btn_add' => 'Добавить нового слушателя'
                        ])
                    ->end()
                ->end()
            ;
        }

    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->tab('Информация')
                ->with('Информация о группе')
                    ->add('course', 'entity', [
                        'label' => 'Название курса',
                        'associated_property' => 'courseName',
                        'route' => [
                            'name' => 'show'
                        ]
                    ])
                    ->add('number', null, [
                        'label' => 'Номер группы'
                    ])
                    ->add('title', null, [
                        'label' => 'Название группы'
                    ])
                    ->add('createdAt', 'date', [
                        'label' => 'Дата начала',
                        'format' => 'd.m.Y H:i'
                    ])
                    ->add('daysLessons', 'choice', [
                        'label' => 'Дни занятий',
                        'multiple' => true,
                        'choices' => [
                            1 => 'Пн',
                            2 => 'Вт',
                            3 => 'Ср',
                            4 => 'Чт',
                            5 => 'Пт',
                            6 => 'Сб',
                            7 => 'Вс'
                        ]
                    ])
                ->end()
                ->with('Преподаватель', ['class' => 'col-md-6 col-lg-6'])
                    ->add('teacher')
                ->end()
                ->with('Куратор', ['class' => 'col-md-6 col-lg-6'])
                    ->add('curator')
                ->end()
            ->end()
            ->tab('Слушатели')
                ->with('Список слушателей')
                    ->add('users')
                ->end()
            ->end()
        ;
    }

    public function toString($groups)
    {
        return $groups instanceof Groups
            ? $groups->getGroupName()
            : 'Группа'
        ;
    }

    private function getUsers($role = null, $id = null)
    {
        $users = $this
            ->getModelManager()
            ->getEntityManager(Users::class)
            ->createQueryBuilder()
            ->select('u')
            ->from(Users::class, 'u');

        if ($role) {
            $users
                ->where('u.roles LIKE :role')
                ->setParameter('role', '%' . $role . '%');
        } elseif ($id) {
            $users
                ->where('u.id = :id')
                ->setParameter('id', $id);
        }

        return $users;
    }

    private function getCourse($id)
    {
        return $this
            ->getModelManager()
            ->getEntityManager(Course::class)
            ->createQueryBuilder()
            ->select('c')
            ->from(Course::class, 'c')
            ->where('c.id = :id')
            ->setParameter('id', $id)
        ;
    }
}
