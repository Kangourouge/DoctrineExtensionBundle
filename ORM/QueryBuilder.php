<?php

namespace KRG\DoctrineExtensionBundle\ORM;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\Select;

class QueryBuilder extends \Doctrine\ORM\QueryBuilder
{
    public function getQuery(bool $autoJoin = false)
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
        $query = parent::getQuery();
        // $query->setHydrationMode('recuresive_array');
        return $query;
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