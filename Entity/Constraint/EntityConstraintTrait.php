<?php

namespace KRG\DoctrineExtensionBundle\Entity\Constraint;

use Doctrine\Common\Collections\Collection;
use KRG\DoctrineExtensionBundle\Entity\Constraint\ConstraintInterface;

trait EntityConstraintTrait
{
     /**
     * @var Collection
     */
    protected $constraints;

    /**
     * @var array
     */
    private $_constraints;

    /**
     * @return Collection
     */
    public function getConstraints()
    {
        return $this->constraints;
    }

    /**
     * @param Constraint $constraint
     *
     * @return ConstraintInterface
     */
    public function addConstraint(ConstraintInterface $constraint)
    {
        $this->constraints->add($constraint);

        $constraint->setForeignId($this->getId());
        $constraint->setForeignClass(get_class($this));

        return $this;
    }

    /**
     * @param Constraint $constraint
     *
     * @return ConstraintInterface
     */
    public function removeConstraint(ConstraintInterface $constraint)
    {
        $this->constraints->removeElement($constraint);

        return $this;
    }

    /**
     * @return array
     */
    public function getArrayConstraints()
    {
        return $this->_constraints;
    }

    /**
     * @param array $constraints
     *
     * @return ConstraintInterface
     */
    public function setArrayConstraints(array $constraints)
    {
        $this->_constraints = $constraints;
        return $this;
    }
}