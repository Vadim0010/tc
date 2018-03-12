<?php

namespace AppBundle\Form;

use AppBundle\Entity\Users;
use AppBundle\Entity\Course;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SearchGroupFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('course', EntityType::class, [
                'class' => Course::class,
                'choice_label' => function ($course) {
                    return $course->getCourseName();
                },
                'expanded' => false,
                'multiple' => false,
                'required' => false
            ])
            ->add('name', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Type(['type' => 'string']),
                    new Length([
                        'min' => 2,
                        'max' => 20
                    ])
                ]
            ])
            ->add('teacher', EntityType::class, [
                'class' => Users::class,
                'choice_label' => function ($user) {
                    return $user->getFullName();
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->andWhere('t.roles LIKE :role')
                        ->setParameter('role', '%' . 'ROLE_TEACHER' . '%')
                    ;
                },
                'expanded' => false,
                'multiple' => false,
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'method' => 'GET'
        ]);
    }

    public function getBlockPrefix()
    {
        return 'search_group_form_type';
    }
}
