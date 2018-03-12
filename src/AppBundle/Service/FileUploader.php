<?php

namespace AppBundle\Service;


use Cocur\Slugify\Slugify;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    /**
     * @var
     */
    private $ha_path;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Slugify
     */
    private $slug;

    public function __construct($ha_path, Logger $logger, Slugify $slug)
    {
        $this->ha_path = $ha_path['$ha_path'];
        $this->logger = $logger;
        $this->slug = $slug;
    }

    /**
     * Загрузить файл на сервер
     *
     * @param UploadedFile $file
     * @param $path
     * @return string
     */
    public function upload(UploadedFile $file, $path)
    {
        $full_path = $this->getHaPath() . $path;
        $fileName = uniqid() . '-' . $this->getFileName($file->getClientOriginalName());

        $file->move($full_path, $fileName);

        return $path . '/' . $fileName;
    }

    /**
     * @return mixed
     */
    public function getHaPath()
    {
        return $this->ha_path;
    }

    /**
     * Получить имя файла
     *
     * @param $file
     * @return string
     */
    private function getFileName($file)
    {
        $name = pathinfo($file, PATHINFO_FILENAME);
        $ext = pathinfo($file, PATHINFO_EXTENSION);

        return sprintf('%s.%s',
            $this->slug->slugify($name),
            $ext
        );
    }
}