<?php

namespace AppBundle\Controller;

use AppBundle\Entity\LessonsUsers;
use AppBundle\Entity\Users;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AccountantController extends Controller
{
    /**
     * @Route("/accountant-management", name="accountant")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $teachers = $em->getRepository(Users::class)->getAllUsersDependenceRole('ROLE_TEACHER');

        return $this->render('SonataAdminBundle::accountant_layout.html.twig', ['teachers' => $teachers]);
    }

    /**
     * @Route(
     *     "/accountant-management/hours",
     *     name="accountant-hours",
     *     methods={"POST"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function getNumberHours(Request $request)
    {
        $logger = $this->get('logger');
        $em = $this->getDoctrine()->getManager();

        try {

            if ($request->isXmlHttpRequest()) {
                $teacher_id = $request->get('data');
                $teacher = is_numeric($teacher_id) ? $em->getRepository(Users::class)->find($teacher_id) : null;

                if (! $teacher) {
                    return new Response('Не удалось найти преподавателя', 400);
                }

                $data = [];
                $date = new \DateTime('midnight first day of this month -1 year');
                $lessons = $em->getRepository(LessonsUsers::class)->getListLessonsForTeacher($teacher, $date);

                if (count($lessons) == 0) {
                    $data['lessons'] = false;
                    $data['message'] = sprintf('%s за последний год не провел(а) ни одного занятия', $teacher->getName());
                    return new JsonResponse(json_encode($data),200);
                }

                $data['lessons'] = $this->get('app.explode_lessons')->explodeLessonsByMonths($lessons);
                return new JsonResponse(json_encode($data),200);
            }

            throw new \Exception();

        } catch (\Exception $e) {
            $logger->error('It is not possible to obtain a time sheet for the teacher who has the id: '. $request->get('data_user') . '. Error in getNumberHours() function; Error is: '. $e);

            return new Response('Внутренняя ошибка сервера.', 500);
        }
    }
}
