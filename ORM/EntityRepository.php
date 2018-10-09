<?php

namespace KRG\DoctrineExtensionBundle\ORM;

use Doctrine\ORM\Query\Expr\Join;

class EntityRepository extends \Doctrine\ORM\EntityRepository
{
    public function createQueryBuilder($alias, $indexBy = null, array $properties = null)
    {
        $queryBuilder = parent::createQueryBuilder($alias);

        if ($properties === null) {
            return $queryBuilder;
        }

        $queryBuilder->resetDQLPart('select');

        foreach ($properties as $property => $propertyPath) {
            $propertyPath = preg_split('/\./', $propertyPath);
            $currentAlias = $alias;
            $fieldName = array_pop($propertyPath);
            if (count($propertyPath) > 0) {
                foreach ($propertyPath as $assoc) {
                    $joinType = substr($assoc, 0, 1 ) === '?' ? Join::LEFT_JOIN : Join::INNER_JOIN;
                    if ($joinType === Join::LEFT_JOIN) {
                        $assoc = substr($assoc, 1);
                    }

                    if (!in_array($assoc, $queryBuilder->getAllAliases())) {
                        if ($joinType === Join::LEFT_JOIN) {
                            $queryBuilder->leftJoin(sprintf('%s.%s', $currentAlias, $assoc), $assoc);
                        } else {
                            $queryBuilder->innerJoin(sprintf('%s.%s', $currentAlias, $assoc), $assoc);
                        }
                    }

                    $currentAlias = $assoc;
                }
            }
            $queryBuilder->addSelect(sprintf('%s.%s as %s', $currentAlias, $fieldName, $property));
        }

        return $queryBuilder;
    }
}