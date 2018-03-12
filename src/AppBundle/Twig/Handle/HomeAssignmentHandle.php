<?php

namespace AppBundle\Twig\Handle;


trait HomeAssignmentHandle
{
    public function getFilename($path)
    {
        if (isset($path) && is_string($path)) {
            $array = explode('/', $path);
            preg_match('/\w+-([\s\w\-\.]+)/', end($array), $filename);

            return $filename[1] ?? $path;
        }

        return 'Файл не найден';
    }

    public function file_exists($path)
    {
        if (file_exists($path)) {
            return true;
        }
        return false;
    }
}