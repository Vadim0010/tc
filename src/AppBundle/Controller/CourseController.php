<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Course;
use AppBundle\Entity\Lessons;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;

class CourseController extends CRUDController
{
    public function createAction()
    {
        $request = $this->getRequest();
        // the key used to lookup the template
        $templateKey = 'edit';

        $this->admin->checkAccess('create');

        $class = new \ReflectionClass($this->admin->hasActiveSubClass() ? $this->admin->getActiveSubClass() : $this->admin->getClass());

        if ($class->isAbstract()) {
            return $this->render(
                'SonataAdminBundle:CRUD:select_subclass.html.twig',
                array(
                    'base_template' => $this->getBaseTemplate(),
                    'admin' => $this->admin,
                    'action' => 'create',
                ),
                null,
                $request
            );
        }

        $object = $this->admin->getNewInstance();

        $preResponse = $this->preCreate($request, $object);
        if ($preResponse !== null) {
            return $preResponse;
        }

        $this->admin->setSubject($object);

        /** @var $form \Symfony\Component\Form\Form */
        $form = $this->admin->getForm();
        $form->setData($object);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            //TODO: remove this check for 4.0
            if (method_exists($this->admin, 'preValidate')) {
                $this->admin->preValidate($object);
            }
            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
                $this->admin->checkAccess('create', $object);

                try {
                    $number_lessons = $form->get('number_lessons')->getData();

                    if ($number_lessons > 0) {
                        $title = 'Занятие №';

                        for ($i = 1; $i <= $number_lessons; $i++) {
                            $lesson = new Lessons();
                            $lesson->setTitle($title . $i);
                            $object->addLesson($lesson);
                        }
                    }

                    $object = $this->admin->create($object);

                    if ($this->isXmlHttpRequest()) {
                        return $this->renderJson(array(
                            'result' => 'ok',
                            'objectId' => $this->admin->getNormalizedIdentifier($object),
                        ), 200, array());
                    }

                    $this->addFlash(
                        'sonata_flash_success',
                        $this->trans(
                            'flash_create_success',
                            array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                            'SonataAdminBundle'
                        )
                    );

                    // redirect to edit mode
                    return $this->redirectTo($object);
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
                            array('%name%' => $this->escapeHtml($this->admin->toString($object))),
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
        // set the theme for the current Admin Form
        $this->setFormTheme($formView, $this->admin->getFormTheme());

        return $this->render($this->admin->getTemplate($templateKey), array(
            'action' => 'create',
            'form' => $formView,
            'object' => $object,
        ), null);
    }

    protected function preShow(Request $request, $object)
    {
        if ($object instanceof Course) {
            $groups = [];
            $date = new \DateTime();
            $paginator = $this->container->get('knp_paginator');
            $current_date = $date->add(new \DateInterval('P1D'))->format('Y.m.d H:i:s');

            $groups_active = $this->getAllGroupsForCurrentCourse($object, '>=', $current_date);
            $groups_future = $this->getAllGroupsForCurrentCourse($object, '<', $current_date);
            $groups_history = $this->getAllGroupsForCurrentCourse($object, 'delete', $current_date);

            $groups['groups_active'] = $paginator->paginate(
                $groups_active,
                $request->query->getInt('groups_active', 1),
                25,
                ['pageParameterName' => 'groups_active']
            );

            $groups['groups_future'] = $paginator->paginate(
                $groups_future,
                $request->query->getInt('groups_future', 1),
                25,
                ['pageParameterName' => 'groups_future']
            );

            $groups['groups_history'] = $paginator->paginate(
                $groups_history,
                $request->query->getInt('groups_history', 1),
                25,
                ['pageParameterName' => 'groups_history']
            );

            $object->groups = $groups;
        }
    }

    public function getAllGroupsForCurrentCourse($course, $operation = '', $date)
    {
         return $course->getGroup()->filter(function ($group) use($date, $operation) {
             switch ($operation) {
                 case '>=':
                     return $group->getCreatedAt() >= $date && $group->getIsCompleted() == 0;
                 case '<':
                     return $group->getCreatedAt() < $date && $group->getIsCompleted() == 0;
                 case 'delete':
                     return $group->getIsCompleted() == 1;
                 default:
                     return true;
             }
        });
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
}
