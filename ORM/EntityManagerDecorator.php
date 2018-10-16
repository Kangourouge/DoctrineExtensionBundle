<?php

namespace KRG\DoctrineExtensionBundle\ORM;

use Doctrine\ORM\EntityManagerInterface;

class EntityManagerDecorator extends \Doctrine\ORM\Decorator\EntityManagerDecorator
{
    public function getRepository($className)
    {
        return $this->getConfiguration()->getRepositoryFactory()->getRepository($this, $className);
    }

    public function createQueryBuilder()
    {
        return new QueryBuilder($this);
    }
}