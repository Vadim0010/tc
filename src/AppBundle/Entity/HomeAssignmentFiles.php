<?php

namespace AppBundle\Entity;

use AppBundle\Entity\HomeAssignment;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="home_assignment_files")
 */
class HomeAssignmentFiles
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\HomeAssignment", inversedBy="files")
     */
    private $homeAssignment;

    /**
     * @ORM\Column(type="string", length=500)
     */
    private $path;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set path
     *
     * @param string $path
     *
     * @return HomeAssignmentFiles
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set homeAssignment
     *
     * @param HomeAssignment $homeAssignment
     *
     * @return HomeAssignmentFiles
     */
    public function setHomeAssignment(HomeAssignment $homeAssignment = null)
    {
        $this->homeAssignment = $homeAssignment;

        return $this;
    }

    /**
     * Get homeAssignment
     *
     * @return HomeAssignment
     */
    public function getHomeAssignment()
    {
        return $this->homeAssignment;
    }
}
