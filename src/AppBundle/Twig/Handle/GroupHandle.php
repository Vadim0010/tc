<?php

namespace AppBundle\Twig\Handle;

use AppBundle\Entity\Groups;
use AppBundle\Entity\Lessons;
use Doctrine\Common\Collections\ArrayCollection;

trait GroupHandle
{
    /**
     * Данные группы
     */
    private $data;

    /**
     * Текущий урок
     */
    private $currentLesson;

    /**
     * Получить данные группы
     *
     * @param Groups $group
     * @return ArrayCollection
     */
    public function getGroupData(Groups $group)
    {
        $lessonsGroup = $group->getLessons();
        list($homeAssignment, $currentLesson) = $this->getCurrentLesson($group, $lessonsGroup);

        $this->setData('lessonsGroup', $lessonsGroup);
        $this->setData('currentLesson', $currentLesson);
        $this->setData('homeAssignment', $homeAssignment);

        return $this->data;
    }

    public function getLessonDate(Groups $group, Lessons $lesson)
    {
        $lessonGroup = $group
            ->getLessonsGroup()
            ->filter( function ($l) use ($lesson) {
                    return $l->getLesson() == $lesson;
                }
            )
            ->first()
        ;

        if ($lessonGroup) {
            return $lessonGroup->getCreatedAt();
        }

        return false;
    }

    /**
     * @param string $key
     * @param $data
     */
    public function setData(string $key, $data)
    {
        $this->data[$key] = $data;
    }

    public function checkLessonPassed(Lessons $lesson, Groups $group)
    {
        return $group->getLessons()->contains($lesson);
    }

    /**
     * Получить данные текущего урока
     *
     * @param Groups $group
     * @param $lessonsGroup
     * @return mixed
     */
    private function getCurrentLesson(Groups $group, $lessonsGroup)
    {
        $lessons = $group->getCourse()->getLessons();

        if ($lessonsGroup->count()) {
            $last_lesson = $group->getLessonsGroup()->last();

            if (date('d.m.Y') == $last_lesson->getCreatedAt()->format('d.m.Y') || $group->getIsCompleted()) {
                return [
                    $last_lesson->getHomeAssignment(),
                    $last_lesson->getLesson()
                ];
            }

            foreach ($lessons as $lesson) {
                if (! $lessonsGroup->contains($lesson)) {
                    $this->currentLesson = $lesson;
                    break;
                }
            }
        } else {
            $this->currentLesson = $lessons->first();
        }

        return [
            null,
            $this->currentLesson
        ];
    }
}