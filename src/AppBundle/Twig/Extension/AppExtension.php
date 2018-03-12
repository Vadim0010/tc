<?php

namespace AppBundle\Twig\Extension;

use AppBundle\Twig\Handle\GroupHandle;
use AppBundle\Twig\Handle\HomeAssignmentHandle;
use AppBundle\Twig\Handle\UserHandle;

class AppExtension extends \Twig_Extension
{
    use GroupHandle, UserHandle, HomeAssignmentHandle;

    public function getName()
    {
        return 'app_extension';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'getGroupData',
                [$this, 'getGroupData']
            ),
            new \Twig_SimpleFunction(
                'getUserData',
                [$this, 'getUserData']
            ),
            new \Twig_SimpleFunction(
                'getLessonDate',
                [$this, 'getLessonDate']
            ),
            new \Twig_SimpleFunction(
                'getFilename',
                [$this, 'getFilename']
            ),
            new \Twig_SimpleFunction(
                'file_exists',
                [$this, 'file_exists']
            ),
            new \Twig_SimpleFunction(
                'getGroupsForChild',
                [$this, 'getGroupsForChild']
            ),
            new \Twig_SimpleFunction(
                'checkLessonPassed',
                [$this, 'checkLessonPassed']
            )
        ];
    }
}
