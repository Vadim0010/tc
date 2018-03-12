<?php

namespace AppBundle\Service;

use AppBundle\Entity\LessonsUsers;

class ExplodeLessons
{
    /**
     * Разбить уроки по месяцам
     *
     * @param $all_lessons
     * @return array
     */
    public function explodeLessonsByMonths($all_lessons) : array
    {
        $data_lessons = [];

        foreach ($all_lessons as $lesson) {
            $year = $lesson->getCreatedAt()->format('Y');
            $month = $lesson->getCreatedAt()->format('m');

            if ( ! array_key_exists($year, $data_lessons) ) {
                $data_lessons = $this->getLessonData($data_lessons, $lesson, $year, $month);
            } else {
                if ( ! array_key_exists($month, $data_lessons[$year]) ) {
                    $data_lessons = $this->getLessonData($data_lessons, $lesson, $year, $month);
                } else {
                    $data_lessons = $this->getLessonData($data_lessons, $lesson, $year, $month, true);
                }
            }
        }

        return $data_lessons;
    }

    /**
     * Получить данные урока
     *
     * @param array $data
     * @param LessonsUsers $lesson
     * @param string $year
     * @param string $month
     * @param bool $add
     * @return array
     */
    private function getLessonData(array $data, LessonsUsers $lesson, string $year, string $month, $add = false) : array
    {
        $hours = $lesson->getLesson()->getCourse()->getDurationLessons() ?? 0;
        $groupId = $lesson->getGroup()->getId();

        if ($add) {
            $data[$year][$month]['hours'] += $hours;

            if ( array_key_exists($groupId, $data[$year][$month]['groups']) ) {
                $data[$year][$month]['groups'][$groupId]['hours'] += $hours;
            } else {
                $data[$year][$month]['groups'][$groupId]['data'] = $this->getGroupData($lesson->getGroup());
                $data[$year][$month]['groups'][$groupId]['hours'] = $hours;
            }

            return $data;
        }

        $data[$year][$month]['hours'] = $hours;
        $data[$year][$month]['groups'][$groupId]['data'] = $this->getGroupData($lesson->getGroup());
        $data[$year][$month]['groups'][$groupId]['hours'] = $hours;

        return $data;
    }

    private function getGroupData($group)
    {
        return [
            'title' => sprintf(
                '№ %s - %s (%s)',
                $group->getNumber() ?? '',
                $group->getTitle() ?? 'нет названия',
                count($group->getUsers())
            ),
            'completed' => $group->getIsCompleted()
        ];
    }
}