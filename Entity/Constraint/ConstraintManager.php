<?php

namespace KRG\DoctrineExtensionBundle\Entity\Constraint;

use KRG\DoctrineExtensionBundle\DBAL\ConstraintEnumType;

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

    public static function check(array $constraints, array $data)
    {
        foreach($constraints as $class => $constraint) {
            if (
                (
                    isset($constraint[ConstraintEnumType::INCLUDE])
                    && (
                            !isset($data[$class])
                        ||  !in_array($data[$class], $constraint[ConstraintEnumType::INCLUDE])
                    )
                )
                ||
                (
                    isset($constraint[ConstraintEnumType::EXCLUDE])
                    && isset($data[$class])
                    && in_array($data[$class], $constraint[ConstraintEnumType::EXCLUDE])
                )
            ) {
                return false;
            }
        }
        return true;
    }
}