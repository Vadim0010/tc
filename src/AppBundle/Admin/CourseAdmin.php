<?php

namespace AppBundle\Admin;

use AppBundle\Classes\Admin;
use AppBundle\Entity\Course;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class CourseAdmin extends Admin
{
    protected $searchResultActions = ['show'];

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, ['label' => 'Название'], 'text', [])
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, [
                'label' => 'Название курса',
                'route' => [
                    'name' => 'show'
                ]
            ])
            ->add('number_lessons', null, [
                'label' => 'Количество занятий'
            ])
            ->add('duration_lessons', null, [
                'label' => 'Продолжительность занятий'
            ])
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Добавить новый курс', ['class' => 'col-md-8 col-lg-8'])
                ->add('title', null, [
                    'label' => 'Название курса'
                ])
                ->add('description', null, [
                    'label' => 'Описание курса',
                    'required' => false
                ])
            ->end();

        if ($this->request->get('_route') != 'admin_app_course_edit') {
            $formMapper
                ->with('Занятия', ['class' => 'col-md-4 col-lg-4'])
                    ->add('number_lessons', IntegerType::class, [
                        'label' => 'Укажите количество занятий',
                        'data' => 10
                    ])
                ->add('durationLessons', IntegerType::class, [
                        'label' => 'Укажите продолжительность занятия, ч',
                        'data' => 4
                    ])
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
            ->tab('Описание')
                ->with('Информация о курсе')
                    ->add('title', null, [
                        'label' => 'Название курса'
                    ])
                    ->add('description', null, [
                        'label' => 'Описание курса'
                    ])
                    ->add('number_lessons', null, [
                        'label' => 'Количество занятий'
                    ])
                    ->add('duration_lessons', null, [
                        'label' => 'Продолжительность занятия'
                    ])
                    ->add('createdAt', 'date', [
                        'label' => 'Дата создания',
                        'format' => 'd.m.Y'
                    ])
                ->end()
            ->end()
            ->tab('Занятия')
                ->with('Список занятий')
                    ->add('lessons')
                ->end()
            ->end()
            ->tab('Группы')
                ->with('Список групп')
                    ->add('group')
                ->end()
            ->end()
        ;
    }

    public function toString($course)
    {
        return $course instanceof Course
            ? $course->getCourseName()
            : 'Курс';
    }
}
