<?php

namespace KRG\DoctrineExtensionBundle\Entity\Constraint;

class ConstraintManager
{
    public static function format(array $constraints)
    {
        $data = [];
        foreach($constraints as $constraint) {
            if ($constraint instanceof ConstraintInterface) {
                $constraint = $constraint->toArray();
            }

            $type = $constraint['type'];
            $entityId = $constraint['entityId'];
            $entityClass = $constraint['entityClass'];

            if (!isset($data[$entityClass])) {
                $data[$entityClass] = [];
            }

            if (!isset($data[$entityClass][$type])) {
                $data[$entityClass][$type] = [];
            }

            $data[$entityClass][$type][] = $entityId;
        }

        return $data;
    }
}