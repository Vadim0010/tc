<?php

namespace AppBundle\Entity;

use AppBundle\Entity\LessonsUsers;
use AppBundle\Entity\Users;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use AppBundle\Entity\Course;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GroupsRepository")
 * @ORM\Table(name="`groups`")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @UniqueEntity(
 *     fields={"number"}
 * )
 */
class Groups
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(
     *     max="50"
     * )
     */
    private $number;

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
     * @ORM\Column(type="json_array", length=100, nullable=true)
     * @Assert\Length(
     *     max="100"
     * )
     */
    private $daysLessons;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $isCompleted;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Course", inversedBy="group")
     * @ORM\JoinColumn(nullable=true)
     */
    private $course;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Users", inversedBy="teacherGroup")
     * @ORM\JoinColumn(nullable=true)
     */
    private $teacher;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Users", inversedBy="curatorGroup")
     * @ORM\JoinColumn(nullable=true)
     */
    private $curator;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Users", inversedBy="user_groups")
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\LessonsUsers", mappedBy="group", cascade={"persist"})
     */
    private $lessonsGroup;

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


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->lessonsGroup = new ArrayCollection();
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
     * @return Groups
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Groups
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
     * @return Groups
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
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     *
     * @return Groups
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
     * Set course
     *
     * @param Course $course
     *
     * @return Groups
     */
    public function setCourse(Course $course = null)
    {
        $this->course = $course;

        return $this;
    }

    /**
     * Get course
     *
     * @return Course
     */
    public function getCourse()
    {
        return $this->course;
    }

    /**
     * Set teacher
     *
     * @param Users $teacher
     *
     * @return Groups
     */
    public function setTeacher(Users $teacher = null)
    {
        $this->teacher = $teacher;

        return $this;
    }

    /**
     * Get teacher
     *
     * @return Users
     */
    public function getTeacher()
    {
        return $this->teacher;
    }

    /**
     * Set curator
     *
     * @param Users $curator
     *
     * @return Groups
     */
    public function setCurator(Users $curator = null)
    {
        $this->curator = $curator;

        return $this;
    }

    /**
     * Get curator
     *
     * @return Users
     */
    public function getCurator()
    {
        return $this->curator;
    }

    /**
     * Add user
     *
     * @param Users $user
     *
     * @return Groups
     */
    public function addUser(Users $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param Users $user
     */
    public function removeUser(Users $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set number
     *
     * @param string $number
     *
     * @return Groups
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set daysLessons
     *
     * @param array $daysLessons
     *
     * @return Groups
     */
    public function setDaysLessons($daysLessons)
    {
        $this->daysLessons = json_encode($daysLessons);

        return $this;
    }

    /**
     * Get daysLessons
     *
     * @return array
     */
    public function getDaysLessons()
    {
        return json_decode($this->daysLessons);
    }

    /**
     * Set isCompleted
     *
     * @param boolean $isCompleted
     *
     * @return Groups
     */
    public function setIsCompleted($isCompleted)
    {
        $this->isCompleted = $isCompleted;

        return $this;
    }

    /**
     * Get isCompleted
     *
     * @return boolean
     */
    public function getIsCompleted()
    {
        return $this->isCompleted;
    }

    /**
     * Add lessonsGroup
     *
     * @param LessonsUsers $lessonsGroup
     *
     * @return Groups
     */
    public function addLessonsGroup(LessonsUsers $lessonsGroup)
    {
        $this->lessonsGroup[] = $lessonsGroup;
        $lessonsGroup->setGroup($this);

        return $this;
    }

    /**
     * Remove lessonsGroup
     *
     * @param LessonsUsers $lessonsGroup
     */
    public function removeLessonsGroup(LessonsUsers $lessonsGroup)
    {
        $this->lessonsGroup->removeElement($lessonsGroup);
        $lessonsGroup->setGroup();
    }

    /**
     * Get lessonsGroup
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLessonsGroup()
    {
        return $this->lessonsGroup;
    }

    public function getLessons()
    {
        $lessons = new ArrayCollection();

        foreach ($this->getLessonsGroup() as $lesson) {
            if ( ! $lessons->contains($lesson->getLesson())) {
                $lessons[] = $lesson->getLesson();
            }
        }

        return $lessons;
    }

    /**
     * Получить название группы
     *
     * @return string
     */
    public function getGroupName()
    {
        return sprintf('Группа № %s - %s',
            $this->getNumber(),
            $this->getTitle()
        );
    }
}
