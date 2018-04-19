<?php

namespace KRG\DoctrineExtensionBundle\Mapping;

use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;

class NamingStrategy extends UnderscoreNamingStrategy
{
    public function joinKeyColumnName($entityName, $referencedColumnName = null)
    {
        return preg_replace( '/interface_/', '', parent::joinKeyColumnName($entityName, $referencedColumnName));
    }
}
