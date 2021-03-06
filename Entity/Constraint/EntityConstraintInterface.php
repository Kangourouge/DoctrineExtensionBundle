<?php

namespace KRG\DoctrineExtensionBundle\Entity\Constraint;

use Doctrine\Common\Collections\Collection;

interface EntityConstraintInterface
{
    /**
     * @return Collection
     */
    public function getConstraints();

    /**
     * @param ConstraintInterface $constraint
     *
     * @return EntityConstraintInterface
     */
    public function addConstraint(ConstraintInterface $constraint);

    /**
     * @param ConstraintInterface $constraint
     *
     * @return EntityConstraintInterface
     */
    public function removeConstraint(ConstraintInterface $constraint);

    /**
     * @return array
     */
    public function getArrayConstraints();

    /**
     * @return array
     */
    public static function getConstraintClasses();
}