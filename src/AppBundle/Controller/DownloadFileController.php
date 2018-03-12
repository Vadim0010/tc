<?php

namespace AppBundle\Controller;

use AppBundle\Entity\HomeAssignmentFiles;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;

class DownloadFileController extends Controller
{
    /**
     * @Route("/download/{ha_file}",
     *     name="download-file",
     *     requirements={"ha_file": "\d+"}
     * )
     * @param HomeAssignmentFiles $ha_file
     * @return BinaryFileResponse|Response
     */
    public function downloadFileAction(HomeAssignmentFiles $ha_file)
    {
        $full_path = $this->getParameter('home_assignment_directory') . $ha_file->getPath();

        if (!file_exists($full_path)) {
            return new Response('Файл не найден', 404);
        }

        $file = new File($full_path);

        return $this->file($file);
    }
}
