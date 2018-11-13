<?php

namespace KRG\DoctrineExtensionBundle\ORM;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Select;
use KRG\DoctrineExtensionBundle\Entity\Constraint\EntityConstraintInterface;
use KRG\DoctrineExtensionBundle\ORM\Query\Filter\ConstraintFilter;

class QueryBuilder extends \Doctrine\ORM\QueryBuilder
{
    public function getQuery()
    {
        /** @var Query\Parameter $parameter */
        foreach($this->getParameters() as $parameter) {
            if ($parameter->getValue() instanceof EntityConstraintInterface) {
                /** @var ConstraintFilter $filter */
                $filter = $this->getEntityManager()->getFilters()->getFilter('constraint_filter');
                $filter->setConstraints($parameter->getValue()->getArrayConstraints(), true);
            }
        }

        $query = parent::getQuery();

        return $query;
    }

    private function getAutoJoinQuery()
    {
        if (!$autoJoin) {
            return parent::getQuery();
        }

        $select = [];
        $selectParts = parent::getDQLPart('select');
        parent::resetDQLPart('select');

        /** @var Select $select */
        foreach($selectParts as $selectPart) {
            foreach($selectPart->getParts() as $idx => $part) {
                $field = $part;
                if (preg_match('/^(.+)\ as\ (.+)$/i', $part, $match)) {
                    $field = $match[2];
                    $part = $match[1];
                }

                if (preg_match_all('|([a-z0-9_]+[\.][a-z0-9_\.\?]+)|i', $part, $matches)) {
                    if (count($matches[0]) > 1 && $field === $part) {
                        $field = sprintf('field_%s', $idx);
                    }
                    foreach($matches[0] as $j => $propertyPath) {
                        $part = str_replace($propertyPath, $this->_addSelectPath($propertyPath), $part);
                    }

                    $select[preg_replace('/[^a-z0-9]/i', '_', $field)] = $part;
                }
            }
        }

        foreach ($select as $key => $value) {
            parent::addSelect(sprintf('%s as %s', $value, $key));
        }

        // $query->setHydrationMode('recuresive_array');
    }

    public function getAllEntities()
    {
        $classes = array_combine($this->getRootAliases(), $this->getRootEntities());

        $joins = $this->getDQLPart('join');
        /** @var Join $join */
        foreach ($joins as $rootAlias => $_joins) {
            foreach ($_joins as $join) {
                $class = $join->getJoin();
                if (preg_match('/([^\.]+)\.([^\.]+)/', $class, $match)) {
                    if (isset($classes[$match[1]])) {
                        $class = $this->getEntityManager()->getClassMetadata($classes[$match[1]])->getAssociationTargetClass($match[2]);
                    }
                }
                $classes[$join->getAlias()] = $class;
            }
        }

        return $classes;
    }

    private function _addSelectPath($property)
    {
        $propertyPath = preg_split('/\./', $property);
        $currentAlias = parent::getRootAliases()[0];
        $fieldName = array_pop($propertyPath);
        if (count($propertyPath) > 0) {
            foreach ($propertyPath as $assoc) {
                $joinType = substr($assoc, 0, 1 ) === '?' ? Join::LEFT_JOIN : Join::INNER_JOIN;
                if ($joinType === Join::LEFT_JOIN) {
                    $assoc = substr($assoc, 1);
                }

                if (!in_array($assoc, parent::getAllAliases())) {
                    if ($joinType === Join::LEFT_JOIN) {
                        parent::leftJoin(sprintf('%s.%s', $currentAlias, $assoc), $assoc);
                    } else {
                        parent::innerJoin(sprintf('%s.%s', $currentAlias, $assoc), $assoc);
                    }
                }

                $currentAlias = $assoc;
            }
        }

        return sprintf('%s.%s', $currentAlias, $fieldName);
    }
}