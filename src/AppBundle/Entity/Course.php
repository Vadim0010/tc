<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Groups;
use AppBundle\Entity\HomeAssignment;
use AppBundle\Entity\Lessons;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CourseRepository")
 * @ORM\Table(name="course")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Course
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min="1",
     *     max="255"
     * )
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="integer")
     * @Assert\Range(
     *     min=1,
     *     max=500
     * )
     */
    private $numberLessons;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\Range(
     *     min=1,
     *     max=24
     * )
     */
    private $durationLessons;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Groups", mappedBy="course", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $group;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Lessons", mappedBy="course", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $lessons;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\HomeAssignment", mappedBy="course", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $homeAssignment;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    public function __construct()
    {
        $this->group = new ArrayCollection();
        $this->lessons = new ArrayCollection();
        $this->homeAssignment = new ArrayCollection();
    }

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
     * Set title
     *
     * @param string $title
     *
     * @return Course
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Course
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set numberLessons
     *
     * @param integer $numberLessons
     *
     * @return Course
     */
    public function setNumberLessons($numberLessons)
    {
        $this->numberLessons = $numberLessons;

        return $this;
    }

    /**
     * Get numberLessons
     *
     * @return integer
     */
    public function getNumberLessons()
    {
        return $this->numberLessons;
    }

    /**
     * Set durationLessons
     *
     * @param integer $durationLessons
     *
     * @return Course
     */
    public function setDurationLessons($durationLessons)
    {
        $this->durationLessons = $durationLessons;

        return $this;
    }

    /**
     * Get durationLessons
     *
     * @return integer
     */
    public function getDurationLessons()
    {
        return $this->durationLessons;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Course
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Course
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Add group
     *
     * @param Groups $group
     *
     * @return Course
     */
    public function addGroup(Groups $group)
    {
        $this->group[] = $group;

        return $this;
    }

    /**
     * Remove group
     *
     * @param Groups $group
     */
    public function removeGroup(Groups $group)
    {
        $this->group->removeElement($group);
    }

    /**
     * Get group
     *
     * @return Collection
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     *
     * @return Course
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Add lesson
     *
     * @param Lessons $lesson
     *
     * @return Course
     */
    public function addLesson(Lessons $lesson)
    {
        $this->lessons[] = $lesson;
        $lesson->setCourse($this);

        return $this;
    }

    /**
     * Remove lesson
     *
     * @param Lessons $lesson
     * @return Course
     */
    public function removeLesson(Lessons $lesson)
    {
        $this->lessons->removeElement($lesson);
        $lesson->setCourse();
    }

    /**
     * Get lessons
     *
     * @return Collection
     */
    public function getLessons()
    {
        return $this->lessons;
    }

    /**
     * Add homeAssignment
     *
     * @param HomeAssignment $homeAssignment
     *
     * @return Course
     */
    public function addHomeAssignment(HomeAssignment $homeAssignment)
    {
        $this->homeAssignment[] = $homeAssignment;
        $homeAssignment->setCourse($this);

        return $this;
    }

    /**
     * Remove homeAssignment
     *
     * @param HomeAssignment $homeAssignment
     */
    public function removeHomeAssignment(HomeAssignment $homeAssignment)
    {
        $this->homeAssignment->removeElement($homeAssignment);
        $homeAssignment->setCourse();
    }

    /**
     * Get homeAssignment
     *
     * @return Collection
     */
    public function getHomeAssignment()
    {
        return $this->homeAssignment;
    }

    public function getCourseName()
    {
        return sprintf('%s (%d ч)',
            $this->getTitle(),
            $this->getNumberLessons() * $this->getDurationLessons()
        );
    }
}
