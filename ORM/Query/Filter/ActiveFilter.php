<?php

namespace KRG\DoctrineExtensionBundle\ORM\Query\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class ActiveFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if ($targetEntity->hasField('active')) {
            return sprintf('%s.%s IS TRUE', $targetTableAlias, $targetEntity->getColumnName('active'));
        }

        return '';
    }
}