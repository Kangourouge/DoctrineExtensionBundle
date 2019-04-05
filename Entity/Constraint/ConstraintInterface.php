<?php

namespace KRG\DoctrineExtensionBundle\Entity\Constraint;

interface ConstraintInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $type
     *
     * @return ConstraintInterface
     */
    public function setType(string $type);

    /**
     * @return string
     */
    public function getEntityClass();

    /**
     * @param string $entityClass
     *
     * @return ConstraintInterface
     */
    public function setEntityClass(string $entityClass);

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param int $entityId
     *
     * @return ConstraintInterface
     */
    public function setEntityId(int $entityId);
}
