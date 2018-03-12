<?php

namespace AppBundle\Controller;

use AppBundle\Entity\LessonsUsers;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TeacherController extends Controller
{
    /**
     * Табель учета рабочего времени
     *
     * @Route("/table",
     *     name="teacher-table"
     * )
     *
     * @return Response
     */
    public function timesheetAction()
    {
        if (! $this->isGranted('ROLE_TEACHER')) {
            throw new AccessDeniedException('У Вас нет прав для просмотра данной страницы!');
        }

        $date = new \DateTime('midnight first day of this month -1 year');
        $em = $this->getDoctrine()->getManager();

        $lessons = $this->get('app.explode_lessons')->explodeLessonsByMonths(
            $em
                ->getRepository(LessonsUsers::class)
                ->getListLessonsForTeacher($this->getUser(), $date)
        );

        return $this->render('SonataAdminBundle:content:teacher_table.html.twig', ['lessons' => $lessons]);
    }
}
