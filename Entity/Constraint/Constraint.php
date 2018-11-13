<?php

namespace KRG\DoctrineExtensionBundle\Entity\Constraint;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use KRG\DoctrineExtensionBundle\DBAL\ConstraintEnumType;

/**
 * @ORM\MappedSuperclass(repositoryClass="KRG\DoctrineExtensionBundle\Repository\ConstraintRepository")
 */
class Constraint implements ConstraintInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var integer
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    protected $foreignId;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $foreignClass;

    /**
     * @ORM\Column(type="constraint_enum")
     * @var string
     */
    protected $type;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $entityClass;

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    protected $entityId;

    /** @var mixed */
    private $entity;

    public function __construct()
    {
        $this->type = ConstraintEnumType::INCLUDE;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getForeignId()
    {
        return $this->foreignId;
    }

    /**
     * @param int $foreignId
     *
     * @return Constraint
     */
    public function setForeignId(int $foreignId)
    {
        $this->foreignId = $foreignId;

        return $this;
    }

    /**
     * @return string
     */
    public function getForeignClass()
    {
        return $this->foreignClass;
    }

    /**
     * @param string $foreignClass
     *
     * @return Constraint
     */
    public function setForeignClass(string $foreignClass)
    {
        $this->foreignClass = $foreignClass;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return Constraint
     */
    public function setType(string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * @param string $entityClass
     *
     * @return Constraint
     */
    public function setEntityClass(string $entityClass)
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    /**
     * @return int
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param int $entityId
     *
     * @return Constraint
     */
    public function setEntityId(int $entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param $entity
     *
     * @return $this
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
        $this->entityId = $entity->getId();
        $this->entityClass = get_class($entity);
        return $this;
    }
}
