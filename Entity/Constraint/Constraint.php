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
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'entityClass' => $this->entityClass,
            'entityId' => $this->entityId
        ];
    }
}
