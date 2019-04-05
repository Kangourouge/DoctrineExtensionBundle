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
        if ($this->_constraints === null) {
            $this->_constraints = ConstraintManager::format($this->constraints->toArray());
        }

        return $this->_constraints;
    }
}