<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Course;
use AppBundle\Entity\Lessons;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LessonController extends Controller
{
    /**
     * Редактировать занятия
     *
     * @Route("/course/lessons/edit",
     *     name="course-lessons-edit",
     *     methods={"PUT"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function editLessonsAction(Request $request)
    {
        $logger = $this->get('logger');
        $em = $this->getDoctrine()->getManager();

        try {
            $data_lessons = json_decode($request->get('data_lessons'));
            $data_course = json_decode($request->get('data_course'));

            if ( $request->isXmlHttpRequest()) {

                if (count($data_lessons)) {

                    $course = $em->getRepository(Course::class)->find($data_course);

                    if ($course) {

                        foreach ($data_lessons as $data) {
                            $lesson = $em->getRepository(Lessons::class)->find($data->lesson_id);
                            $title = trim(strip_tags($data->lesson_title));
                            $body = $this->handleLessonBody($data->lesson_body);

                            if ( ! $lesson ) {
                                return new Response(
                                    'Не удалось найти занятие "' . $data->lesson_title . '" с id: ' . $data->lesson_id,
                                    400
                                );
                            }

                            if ( ! $title) {
                                return new JsonResponse(
                                    [
                                        'status' => 'error',
                                        'message' => 'Поле "Тема" обязательно для заполнения!',
                                        'id' => $data->lesson_id
                                    ],
                                    200
                                );
                            }

                            $lesson->setTitle($title);
                            $lesson->setBody($body);

                            $em->persist($lesson);
                        }

                        $em->flush();
                        return new Response('Данные успешно сохранены!', 200);
                    }

                    return new Response('Не удалось найти данный курс!', 400);
                }

                return new Response( 'Невозможно отредактировать занятия!', 400);
            }

            throw new \Exception();

        } catch (\Exception $e) {
            $logger->error('Could not edit lessons data '. $request->get('data_course') . '. Error in editLessonsAction() function; Error is: '. $e);

            return new Response('Внутренняя ошибка сервера.', 500);
        }
    }

    /**
     * Добавить курсу новое занятие
     *
     * @Route("/course/lessons/add",
     *     name="course-lessons-add",
     *     methods={"POST"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function addLessonAction(Request $request)
    {
        $logger = $this->get('logger');
        $em = $this->getDoctrine()->getManager();

        try {
            $data_course = json_decode($request->get('data_course'));

            if ( $request->isXmlHttpRequest()) {

                $course = $em->getRepository(Course::class)->find($data_course);

                if ($course) {

                    $new_number_lessons = $course->getNumberLessons() + 1;
                    $lesson = new Lessons();

                    $lesson->setTitle('Занятие №' . $new_number_lessons);
                    $course->setNumberLessons($new_number_lessons);
                    $course->addLesson($lesson);
                    $em->persist($course);
                    $em->flush();

                    return new JsonResponse(
                        [
                            'status' => 'success',
                            'message' => 'Новое занятие успешно добавлено!',
                            'number_lesson' => $new_number_lessons,
                            'lesson_id' => $lesson->getId(),
                            'lesson_title' => $lesson->getTitle()
                        ],
                        200
                    );
                }

                return new Response('Не удалось найти данный курс!', 400);
            }

            throw new \Exception();

        } catch (\Exception $e) {
            $logger->error('Unable to add new lesson '. $request->get('data_course') . '. Error in addLessonAction() function; Error is: '. $e);

            return new Response('Внутренняя ошибка сервера.', 500);
        }
    }

    /**
     * Удалить у курса занятие
     *
     * @Route("/course/lessons/delete",
     *     name="course-lessons-delete",
     *     methods={"DELETE"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function deleteLessonAction(Request $request)
    {
        $logger = $this->get('logger');
        $em = $this->getDoctrine()->getManager();

        try {
            $data = json_decode($request->get('data'));

            if ( $request->isXmlHttpRequest()) {

                $course = $em->getRepository(Course::class)->find($data->data_course);

                if ($course) {

                    $new_number_lessons = $course->getNumberLessons() - 1;
                    $lesson = $em->getRepository(Lessons::class)->find($data->data_lesson);

                    if ( ! $lesson) {
                        return new Response(
                            'Не удалось найти занятие с id: ' . $data->data_lesson,
                            400
                        );
                    }

                    $course->setNumberLessons($new_number_lessons);
                    $course->removeLesson($lesson);
                    $em->persist($course);
                    $em->flush();

                    return new Response('Занятие успешно удалено!', 200);
                }

                return new Response('Не удалось найти данный курс!', 400);
            }

            throw new \Exception();

        } catch (\Exception $e) {
            $logger->error('Unable to delete lesson '. $request->get('data_course') . '. Error in deleteLessonsAction() function; Error is: '. $e);

            return new Response('Внутренняя ошибка сервера.', 500);
        }
    }

    private function handleLessonBody($lesson_body)
    {
        $lesson_body = trim($lesson_body);

        if (! $lesson_body) {
            return null;
        }

        return $lesson_body;
    }
}
