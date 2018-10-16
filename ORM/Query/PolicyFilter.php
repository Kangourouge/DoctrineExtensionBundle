<?php

namespace KRG\DoctrineExtensionBundle\ORM\Query;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class PolicyFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        // TODO: Implement addFilterConstraint() method.
    }
}