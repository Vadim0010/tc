<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Groups;
use AppBundle\Entity\LessonsUsers;
use AppBundle\Entity\Users as ParentUser;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UsersRepository")
 * @ORM\Table(name="`users`")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @UniqueEntity(
 *     fields={"email"}
 * )
 */
class Users extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Users")
     * @ORM\JoinColumn(nullable=true)
     */
    private $parent;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min="1",
     *     max="255"
     * )
     */
    private $first_name;

    /**
     * @ORM\Column(type="string",  nullable=true)
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min="1",
     *     max="255"
     * )
     */
    private $last_name;

    /**
     * @ORM\Column(type="string",  nullable=true)
     * @Assert\Length(
     *     max="255"
     * )
     */
    private $middle_name;

    /**
     * @ORM\Column(type="string",  nullable=true)
     * @Assert\NotBlank()
     */
    private $phone;

    /**
     * @ORM\Column(type="string", nullable=true, length=500)
     */
    private $address;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Groups", mappedBy="users")
     */
    private $user_groups;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Groups", mappedBy="teacher")
     */
    private $teacherGroup;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Groups", mappedBy="curator")
     */
    private $curatorGroup;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\LessonsUsers", mappedBy="user", cascade={"persist", "remove"})
     */
    private $lessonsList;

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
        parent::__construct();

        $this->user_groups = new ArrayCollection();
        $this->curatorGroup = new ArrayCollection();
        $this->teacherGroup = new ArrayCollection();
        $this->lessonsList = new ArrayCollection();
    }

    public function setEmail($email)
    {
        $this->setUsername($email);

        return parent::setEmail($email);
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return Users
     */
    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return Users
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Set middleName
     *
     * @param string $middleName
     *
     * @return Users
     */
    public function setMiddleName($middleName)
    {
        $this->middle_name = $middleName;

        return $this;
    }

    /**
     * Get middleName
     *
     * @return string
     */
    public function getMiddleName()
    {
        return $this->middle_name;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return Users
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return Users
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Users
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
     * @return Users
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
     * @return Users
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
     * Set parent
     *
     * @param ParentUser $parent
     *
     * @return Users
     */
    public function setParent(ParentUser $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return ParentUser
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add userGroup
     *
     * @param Groups $userGroup
     *
     * @return Users
     */
    public function addUserGroup(Groups $userGroup)
    {
        $this->user_groups[] = $userGroup;
        $userGroup->addUser($this);

        return $this;
    }

    /**
     * Remove userGroup
     *
     * @param Groups $userGroup
     */
    public function removeUserGroup(Groups $userGroup)
    {
        $this->user_groups->removeElement($userGroup);
        $userGroup->removeUser($this);
    }

    /**
     * Get userGroups
     *
     * @return Collection
     */
    public function getUserGroups()
    {
        return $this->user_groups;
    }

    /**
     * Add teacherGroup
     *
     * @param Groups $teacherGroup
     *
     * @return Users
     */
    public function addTeacherGroup(Groups $teacherGroup)
    {
        $this->teacherGroup[] = $teacherGroup;
        $teacherGroup->setTeacher($this);

        return $this;
    }

    /**
     * Remove teacherGroup
     *
     * @param Groups $teacherGroup
     */
    public function removeTeacherGroup(Groups $teacherGroup)
    {
        $this->teacherGroup->removeElement($teacherGroup);
        $teacherGroup->setTeacher();
    }

    /**
     * Get teacherGroup
     *
     * @return Collection
     */
    public function getTeacherGroup()
    {
        return $this->teacherGroup;
    }

    /**
     * Add curatorGroup
     *
     * @param Groups $curatorGroup
     *
     * @return Users
     */
    public function addCuratorGroup(Groups $curatorGroup)
    {
        $this->curatorGroup[] = $curatorGroup;
        $curatorGroup->setCurator($this);

        return $this;
    }

    /**
     * Remove curatorGroup
     *
     * @param Groups $curatorGroup
     */
    public function removeCuratorGroup(Groups $curatorGroup)
    {
        $this->curatorGroup->removeElement($curatorGroup);
        $curatorGroup->setCurator();
    }

    /**
     * Get curatorGroup
     *
     * @return Collection
     */
    public function getCuratorGroup()
    {
        return $this->curatorGroup;
    }

    /**
     * Add lessonsList
     *
     * @param LessonsUsers $lessonsList
     *
     * @return Users
     */
    public function addLessonsList(LessonsUsers $lessonsList)
    {
        $this->lessonsList[] = $lessonsList;
        $lessonsList->setUser($this);

        return $this;
    }

    /**
     * Remove lessonsList
     *
     * @param LessonsUsers $lessonsList
     */
    public function removeLessonsList(LessonsUsers $lessonsList)
    {
        $this->lessonsList->removeElement($lessonsList);
        $lessonsList->setUser();
    }

    /**
     * Get lessonsList
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLessonsList()
    {
        return $this->lessonsList;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        $fullName = $this->getName();

        if ($fullName) {
            return sprintf('%s. E-mail: %s',
                $fullName,
                $this->email
            );
        } else {
            return $this->email;
        }
    }

    public function getListenerName()
    {
        $fullName = $this->getName();

        $fullName .= $this->phone ? ' (Тел.: ' . $this->phone . ')': '';

        if ($fullName) {
            return $fullName;
        } else {
            return $this->email;
        }
    }

    public function getName()
    {
        $fullName = '';

        $fullName .= $this->last_name ? $this->last_name . ' ' : '';
        $fullName .= $this->first_name ?  $this->first_name . ' ' : '';
        $fullName .= $this->middle_name ?? '';

        return $fullName;
    }
}
