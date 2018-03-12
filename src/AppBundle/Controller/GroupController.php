<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Users;
use AppBundle\Entity\Groups;
use AppBundle\Entity\Lessons;
use AppBundle\Entity\LessonsUsers;
use AppBundle\Service\FileUploader;
use AppBundle\Entity\HomeAssignment;
use AppBundle\Entity\HomeAssignmentFiles;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Twig\Handle\HomeAssignmentHandle;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GroupController extends Controller
{
    use HomeAssignmentHandle;

    /**
     * @Route("/group/{group}/show",
     *     name="group-show",
     *     requirements={"group": "\d+"}
     * )
     *
     * @param Groups $group
     * @return Response
     */
    public function groupShowAction(Groups $group)
    {
        if ( $group->getIsCompleted() ) {
            return $this->redirectToRoute('archive_group-show', ['group' => $group->getId()]);
        }

        if ($this->isGranted(['ROLE_TEACHER', 'ROLE_CURATOR', 'ROLE_ADMIN'])) {
            return $this->render('SonataAdminBundle:content:admin_group_show.html.twig', ['group' => $group]);
        } elseif ($this->isGranted(['ROLE_LISTENER']) && $group->getUsers()->contains($this->getUser())) {
            return $this->render('SonataAdminBundle:content:listener_group_show.html.twig', ['group' => $group, 'listener' => $this->getUser()]);
        }

        throw new NotFoundHttpException();
    }

    /**
     * @Route("/archive/group/{group}/show",
     *     name="archive_group-show",
     *     requirements={"group": "\d+"}
     * )
     *
     * @param Groups $group
     * @return Response
     */
    public function archiveGroupShowAction(Groups $group)
    {
        if ( ! $group || ! $group->getIsCompleted() ) {
            throw $this->createNotFoundException();
        }

        if ($this->isGranted(['ROLE_TEACHER', 'ROLE_CURATOR', 'ROLE_ADMIN'])) {
            return $this->render('SonataAdminBundle:content:admin_group_show.html.twig', ['group' => $group]);
        } elseif ($this->isGranted(['ROLE_LISTENER']) && $group->getUsers()->contains($this->getUser())) {
            return $this->render('SonataAdminBundle:content:listener_group_show.html.twig', ['group' => $group, 'listener' => $this->getUser()]);
        }

        throw new NotFoundHttpException();
    }

    /**
     * Сохранить занятия для данной группы
     *
     * @Route("/group/lesson/save",
     *     name="lesson-save",
     *     methods={"POST"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function saveGroupLessonsAction(Request $request)
    {
        $logger = $this->get('logger');
        $em = $this->getDoctrine()->getManager();
        $responseData = [
            'msg' => '',
            'status' => 200
        ];

        try {
            $data_users = json_decode($request->get('data_user'));
            $data_group = json_decode($request->get('data_group'));

            if ( $request->isXmlHttpRequest() && $this->isGranted('ROLE_TEACHER')) {

                if (count($data_users)) {

                    $group = is_numeric($data_group->current_group) ? $em->getRepository(Groups::class)->find($data_group->current_group) : null;
                    $lesson = is_numeric($data_group->current_lesson) ? $em->getRepository(Lessons::class)->find($data_group->current_lesson) : null;

                    if ( ! $group ) {
                        $responseData['msg'] = 'Не удалось найти группу';

                        throw new \Exception(
                            sprintf(
                                'Could not find group (id = %s). Error in the function %s on line %s',
                                $data_group->current_group ?? 'group_id not found',
                                __METHOD__,
                                __LINE__
                            )
                        );
                    }

                    if ( ! $lesson ) {
                        $responseData['msg'] = 'Не удалось найти урок';

                        throw new \Exception(
                            sprintf(
                                'Could not find lesson (id = %s). Error in the function %s on line %s',
                                $data_group->current_lesson ?? 'lesson_id not found',
                                __METHOD__,
                                __LINE__
                            )
                        );
                    }

                    $lessonsGroup = $group->getLessons()->filter(function($l) use ($lesson) {
                        return $l === $lesson;
                    });

                    $action = $lessonsGroup->count() ? 'edit' : 'create';
                    $lessons_count = $group->getCourse()->getNumberLessons();
                    $lessonsGroup_count = $action == 'create' ? $group->getLessons()->count() + 1 : $group->getLessons()->count();
                    $teacher = $this->getUser();
                    $teacherLessonGroup = $group->getLessonsGroup()->filter(function ($l) use ($lesson) {
                        return $l === $lesson
                            && in_array('ROLE_TEACHER', $l->getUsersList()->getRoles());
                    });

                    foreach ($data_users as $data) {
                        list($user_id, $mark, $isAttend, $comment) = $this->getDataLesson($data);
                        $user = $em->getRepository(Users::class)->find($user_id);

                        if ( ! $user ) {
                            $responseData['msg'] = 'Не удалось найти учащегося с id:' . $data->user_id;

                            throw new \Exception(
                                sprintf(
                                    'Could not find student (id = %s). Error in the function %s on line %s',
                                    $data->user_id ?? 'user_id not found',
                                    __METHOD__,
                                    __LINE__
                                )
                            );
                        }

                        $this->addLessonUser($action, $em, $group, $lesson, $user, $isAttend, $mark, $comment);
                    }

                    if ( ($action === 'edit' && ! $teacherLessonGroup) || $action === 'create' ) {
                        $this->addLessonUser('create', $em, $group, $lesson, $teacher, 1, null, null);
                    }

                    if ( $lessons_count == $lessonsGroup_count ) {
                        $group->setIsCompleted(1);
                        $em->persist($group);
                    }

                    $em->flush();
                    $responseData['msg'] = 'Данные успешно сохранены!';
                } else {
                    $responseData['msg'] = 'В данной группе нет ни одного учащегося!';

                    throw new \Exception(
                        sprintf(
                            'There are no students in this group. Error in the function %s on line %s',
                            __METHOD__,
                            __LINE__
                        )
                    );
                }
            } else {
                throw new \Exception(
                    sprintf(
                        'An error occurred while sending the Ajax request while saving the data in the group. Group data: %s, list of students: %s, teacher: %s. Error in the function %s on line %s',
                        $request->get('data_group') ?? 'could not get the data of the group',
                        $request->get('data_user') ?? 'could not get list of students',
                        $this->getUser() ? $this->getUser()->getId() : 'teacher_id is not found',
                        __METHOD__,
                        __LINE__
                    )
                );
            }
        } catch (\Exception $e) {
            $logger->error($e->getMessage());
            $responseData['msg'] = $responseData['msg'] != '' ? $responseData['msg'] : 'Не удалось сохранить данные';
            $responseData['status'] = 400;
        }

        return new JsonResponse($responseData['msg'], $responseData['status']);
    }

    /**
     * Редактировать выбранный урок
     *
     * @Route("/group/lesson/edit",
     *     name="lesson-edit",
     *     methods={"PUT"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function editSelectedLesson(Request $request)
    {
        $logger = $this->get('logger');
        $em = $this->getDoctrine()->getManager();
        $responseData = [
            'msg' => '',
            'status' => 200
        ];

        try {

            if ( $request->isXmlHttpRequest() && $this->isGranted('ROLE_TEACHER')) {
                $data = json_decode($request->get('data'));
                $group = is_numeric($data->group) ? $em->getRepository(Groups::class)->find($data->group) : null;
                $lesson = is_numeric($data->lesson) ? $em->getRepository(Lessons::class)->find($data->lesson) : null;

                if ( ! $group ) {
                    $responseData['msg'] = 'Не удалось найти группу';

                    throw new \Exception(
                        sprintf(
                            'Could not find group (id = %s). Error in the function %s on line %s',
                            $data->group ?? 'group_id not found',
                            __METHOD__,
                            __LINE__
                        )
                    );
                }

                if ( ! $lesson ) {
                    $responseData['msg'] = 'Не удалось найти урок';

                    throw new \Exception(
                        sprintf(
                            'Could not find lesson (id = %s). Error in the function %s on line %s',
                            $data->lesson ?? 'lesson_id not found',
                            __METHOD__,
                            __LINE__
                        )
                    );
                }

                $users = $group->getUsers();

                if ($users->count()) {
                    $data = [
                        'lesson' => [],
                        'users' => [],
                        'teacher' => []
                    ];
                    $lessonsGroup = $group->getLessonsGroup()->filter(function($l) use($lesson) {
                        return $l->getLesson() === $lesson
                            && in_array('ROLE_LISTENER', $l->getUser()->getRoles());
                    });

                    $data['lesson']['id'] = $lesson->getId();
                    $data['lesson']['title'] = $lesson->getTitle();
                    $data['lesson']['body'] = $lesson->getBody();

                    if( $lessonsGroup->count() ) {
                        $data['lesson']['createdAt'] = $lessonsGroup->first()->getCreatedAt()->format('d.m.Y');
                        $teacher = $group
                            ->getLessonsGroup()
                            ->filter(function($l) use($lesson) {
                                return $l->getLesson() === $lesson
                                    && in_array('ROLE_TEACHER', $l->getUser()->getRoles());
                            });

                        if ($teacher->count()) {
                            $data['teacher']['name'] = $teacher->first()->getUser()->getName();
                        } else {
                            $data['teacher']['name'] = 'Преподаватель не назначен';
                        }

                        foreach ($lessonsGroup as $value) {
                            $data_user = [];
                            $current_user = $value->getUser();

                            $data_user['id'] = $current_user->getId();
                            $data_user['name'] = $current_user->getListenerName();
                            $data_user['isAttend'] = $value->getIsAttend();
                            $data_user['mark'] = $value->getMark();
                            $data_user['comment'] = $value->getComment();

                            array_push($data['users'], $data_user);
                        }

                    } else {
                        $data['lesson']['createdAt'] = date('d.m.Y');
                        $data['teacher']['name'] = $this->getUser()->getName();

                        foreach ($users as $user) {
                            $data_user = [];

                            $data_user['id'] = $user->getId();
                            $data_user['name'] = $user->getListenerName();
                            $data_user['isAttend'] = 0;
                            $data_user['mark'] = null;
                            $data_user['comment'] = null;

                            array_push($data['users'], $data_user);
                        }
                    }

                    $responseData['msg'] = $data;
                } else {
                    $responseData['msg'] = 'В данной группе нет ни одного учащегося!';

                    throw new \Exception(
                        sprintf(
                            'There are no students in this group. Error in the function %s on line %s',
                            __METHOD__,
                            __LINE__
                        )
                    );
                }
            } else {
                throw new \Exception(
                    sprintf(
                        'Failed to send Ajax request when editing a lesson (%s). Error in the function %s on line %s',
                        $request->get('data') ?? 'could not get the data',
                        __METHOD__,
                        __LINE__
                    )
                );
            }
        } catch (\Exception $e) {
            $logger->error($e->getMessage());
            $responseData['msg'] = $responseData['msg'] != '' ? $responseData['msg'] : 'Не удалось получить данные выбранного урока';
            $responseData['status'] = 400;
        }

        return new JsonResponse($responseData['msg'], $responseData['status']);

    }

    /**
     * @Route("/home-assignment/show",
     *     name="home-assignment-show",
     *     methods={"POST"}
     * )
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function getHomeAssignmentAction(Request $request)
    {
        $logger = $this->get('logger');
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $responseData = [
            'msg' => '',
            'status' => 200
        ];

        try {

            if ( $request->isXmlHttpRequest()) {
                $data = json_decode($request->get('data'));
                $group = is_numeric($data->group) ? $em->getRepository(Groups::class)->find($data->group) : null;
                $lesson = is_numeric($data->lesson) ? $em->getRepository(Lessons::class)->find($data->lesson) : null;

                if ( ! $user || ! $this->isGranted(['ROLE_LISTENER', 'ROLE_PARENT', 'ROLE_CHILD'])) {
                    $responseData['msg'] = 'Не удалось получить домашнее задание';

                    throw new \Exception(
                        sprintf(
                            'Could not get the data of the current user (user_id = %s). Error in the function %s on line %s',
                            $user ? $user->getId() : 'user_id not found',
                            __METHOD__,
                            __LINE__
                        )
                    );
                }

                if ( ! $group ) {
                    $responseData['msg'] = 'Не удалось найти группу';

                    throw new \Exception(
                        sprintf(
                            'Could not find group (id = %s). Error in the function %s on line %s',
                            $data->group ?? 'group_id not found',
                            __METHOD__,
                            __LINE__
                        )
                    );
                }

                if ( ! $lesson ) {
                    $responseData['msg'] = 'Не удалось найти урок';

                    throw new \Exception(
                        sprintf(
                            'Could not find lesson (id = %s). Error in the function %s on line %s',
                            $data->lesson ?? 'lesson_id not found',
                            __METHOD__,
                            __LINE__
                        )
                    );
                }

                $data_ha = [];
                $lessonGroup = $group->getLessonsGroup()->filter(function($l) use($lesson, $user) {
                    return $l->getLesson() === $lesson
                        && ($l->getUser() === $user || $l->getUser()->getParent() === $user);
                })->first();

                if ( ! $lessonGroup ) {
                    $responseData['msg'] = 'Данное занятие еще не проводилось';
                    return new JsonResponse($responseData['msg'], $responseData['status']);
                }

                $homeAssignment = $lessonGroup->getHomeAssignment();

                if ( ! $homeAssignment ) {
                    $responseData['msg'] = 'Для данного занятия нет дополнительной информации';
                    return new JsonResponse($responseData['msg'], $responseData['status']);
                }

                $files = $homeAssignment->getFiles();
                $data_ha['title'] = $homeAssignment->getTitle();
                $data_ha['body'] = $homeAssignment->getBody();
                $data_ha['comment'] = ! $this->isGranted('ROLE_CHILD') ? $lessonGroup->getComment() : '';
                $data_ha['files'] = [];

                if( $files->count() ) {
                    foreach ($files as $file) {
                        if($this->file_exists($this->getParameter('home_assignment_directory') . $file->getPath())) {
                            array_push(
                                $data_ha['files'],
                                [
                                    'fileName' => $this->getFilename($file->getPath()),
                                    'path' => $file->getPath(),
                                    'id' => $file->getId()
                                ]
                            );
                        }
                    }
                }

                $responseData['msg'] = $data_ha;
            } else {
                throw new \Exception(
                    sprintf(
                        'Failed to send Ajax request when receiving homework (%s). Error in the function %s on line %s',
                        $request->get('data') ?? 'could not get the data',
                        __METHOD__,
                        __LINE__
                    )
                );
            }

        } catch (\Exception $e) {
            $logger->error($e->getMessage());
            $responseData['msg'] = $responseData['msg'] != '' ? $responseData['msg'] : 'Не удалось получить домашнее задние';
            $responseData['status'] = 400;
        }

        return new JsonResponse($responseData['msg'], $responseData['status']);
    }
    /**
     * @Route("/group/home-assignment/add",
     *     name="home-assignment-add",
     *     methods={"POST"}
     * )
     *
     * @param Request $request
     * @param FileUploader $fileUp
     * @return Response
     */
    public function addHomeAssignmentAction(Request $request, FileUploader $fileUp)
    {
        $logger = $this->get('logger');
        $trans = $this->get('sonata.core.slugify.cocur');
        $em = $this->getDoctrine()->getManager();
        $responseData = [
            'msg' => '',
            'status' => 200
        ];

        try {

            if ( $request->isXmlHttpRequest() && $this->isGranted('ROLE_TEACHER')) {

                list($group_id, $lesson_id, $ha_id, $ha_title, $ha_body, $files, $ha_selected) = $this->getDataHomeAssignment($request);
                $group = is_numeric($group_id) ? $em->getRepository(Groups::class)->find($group_id) : null;
                $lesson = is_numeric($lesson_id) ? $em->getRepository(Lessons::class)->find($lesson_id) : null;
                $homeAssignment = is_numeric($ha_id) ? $em->getRepository(HomeAssignment::class)->find($ha_id) : null;
                $homeAssignmentSelected = is_numeric($ha_selected) ? $em->getRepository(HomeAssignment::class)->find($ha_selected) : null;
                
                if (!$group) {
                    $responseData['msg'] = 'Не удалось найти группу';

                    throw new \Exception(
                        sprintf(
                            'Could not find group (id = %s). Error in the function %s on line %s',
                            $data->group ?? 'group_id not found',
                            __METHOD__,
                            __LINE__
                        )
                    );
                }     

                if (!$lesson) {
                    $responseData['msg'] = 'Не удалось найти урок';

                    throw new \Exception(
                        sprintf(
                            'Could not find lesson (id = %s). Error in the function %s on line %s',
                            $data->lesson ?? 'lesson_id not found',
                            __METHOD__,
                            __LINE__
                        )
                    );
                }

                $course = $group->getCourse();
                $lessonsGroup = $group->getLessonsGroup()->filter(function($l) use ($lesson) {
                    return $l->getLesson() === $lesson;
                });
                $users = $group->getUsers();

                if ($homeAssignmentSelected) {
                    $this->addOrUpdateHomeAssignmentCurrentLesson($lessonsGroup, $homeAssignmentSelected, $users, $group, $lesson, $em);
                    $responseData['msg'] = 'Задание успешно добавлено';
                } else {
                    $path = sprintf('/%s/%s',
                        $trans->slugify($course->getCourseName()),
                        $trans->slugify($lesson ? $lesson->getTitle() : $group->getNumber())
                    );

                    if ($homeAssignment) {
                        $responseData['msg'] = 'Задание успешно обновлено';
                    } else {
                        $homeAssignment = new HomeAssignment();
                        $this->addOrUpdateHomeAssignmentCurrentLesson($lessonsGroup, $homeAssignment, $users, $group, $lesson, $em);
                        $responseData['msg'] ='Задание успешно добавлено';
                    }

                    if ($files) {
                        foreach ($files as $file) {
                            $ha_file = new HomeAssignmentFiles();
                            $file_path = $fileUp->upload($file, $path);
                            $ha_file->setPath($file_path);
                            $homeAssignment->addFile($ha_file);
                            $em->persist($ha_file);
                        }
                    }

                    $homeAssignment->setTitle($ha_title ?? $course->getCourseName() . ' - ' . $lesson->getTitle());
                    $homeAssignment->setBody($ha_body ?? null);
                    $homeAssignment->setCourse($course);
                    $em->persist($homeAssignment);
                }

                $em->flush();
            } else {
                throw new \Exception(
                    sprintf(
                        'Failed to send Ajax request when adding homework. Error in the function %s on line %s',
                        __METHOD__,
                        __LINE__
                    )
                );
            }

        } catch (\Exception $e) {
            $logger->error($e->getMessage());
            $responseData['msg'] = $responseData['msg'] != '' ? $responseData['msg'] : 'Не удалось добавить домашнее задание';
            $responseData['status'] = 400;
        }

        return new JsonResponse($responseData['msg'], $responseData['status']);
    }

    /**
     * Получить данные текущего урока
     *
     * @param $data
     * @return array
     */
    private function getDataLesson($data)
    {
        return [
            $data->user_id,
            $data->user_mark ? (int) $data->user_mark : null,
            $data->user_attend,
            $data->user_comment
        ];
    }

    /**
     * Добавить новый или редактировать существующий урок для текущей группы
     *
     * @param $action
     * @param $em
     * @param $group
     * @param $lesson
     * @param $user
     * @param $isAttend
     * @param $mark
     * @param $comment
     * @param null $ha
     */
    private function addLessonUser($action, $em, $group, $lesson, $user, $isAttend = false, $mark = null, $comment = null, $ha = null)
    {
        $l = new LessonsUsers();

        if ($action === 'create') {
            $l->setGroup($group);
            $l->setLesson($lesson);
            $l->setUser($user);
        } elseif ($action === 'edit') {
            $l = $em
                ->getRepository(LessonsUsers::class)
                ->findBy([
                    'user' => $user,
                    'lesson' => $lesson,
                    'group' => $group
                ])[0]
            ;
        }

        if ($ha) {
            $l->setHomeAssignment($ha);
        }

        $l->setIsAttend($isAttend);
        $l->setMark($mark);
        $l->setComment($comment);
        $em->persist($l);
    }

    /**
     * Поучить данные домашнего задания
     * @param $request
     * @return array
     */
    private function getDataHomeAssignment($request)
    {
        return [
            $request->get('group_id'),
            $request->get('lesson_id'),
            $request->get('ha_id'),
            $request->get('ha_title'),
            $request->get('ha_body'),
            $request->files->get('file'),
            $request->get('ha_selected')
        ];
    }

    /**
     * Добавить домашнее задание к текущему уроку
     *
     * @param $lessonsGroup
     * @param $homeAssignment
     * @param $users
     * @param $group
     * @param $lesson
     * @param $em
     */
    private function addOrUpdateHomeAssignmentCurrentLesson($lessonsGroup, $homeAssignment, $users, $group, $lesson, $em)
    {
        if ($lessonsGroup->count()) {
            foreach ($lessonsGroup as $lessonGroup) {
                $lessonGroup->setHomeAssignment($homeAssignment);
                $em->persist($lessonGroup);
            }
        } else {
            foreach ($users as $user) {
                $this->addLessonUser('create', $em, $group, $lesson, $user, false, null, null, $homeAssignment);
            }
        }
    }
}
