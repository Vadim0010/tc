<?php

namespace AppBundle\Classes;

use Sonata\AdminBundle\Admin\AbstractAdmin;

class Admin extends AbstractAdmin
{
    public function getExportFormats()
    {
        return [
            'csv', 'xls'
        ];
    }
}