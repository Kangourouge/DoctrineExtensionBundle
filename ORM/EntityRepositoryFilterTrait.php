<?php

namespace KRG\DoctrineExtensionBundle\ORM;

use Doctrine\Common\Collections\Collection;

trait EntityRepositoryFilterTrait
{
    protected function addFilter(\Doctrine\ORM\QueryBuilder $queryBuilder, array $filter, array $columns) {
        foreach ($filter as $key => $value) {
            if ($value === null
                || (is_string($value) && strlen($value) === 0)
                || (is_array($value) && count($value) === 0)
                || ($value instanceof Collection && $value->isEmpty())
            ) {
                continue;
            }

            if (isset($columns[$key])) {
                if (is_numeric($value) && (int) $value === 0) {
                    $queryBuilder->andWhere(sprintf('%s is null', $columns[$key]));
                } else {
                    $parameter = $key . uniqid();
                    $operator = is_array($value) || $value instanceof Collection ? 'in' : '=';
                    $queryBuilder->andWhere(sprintf('%s %s (:%s)', $columns[$key], $operator, $parameter))
                        ->setParameter($parameter, $value);
                }
            }
        }
    }
}