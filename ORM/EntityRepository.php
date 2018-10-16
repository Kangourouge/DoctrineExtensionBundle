<?php

namespace KRG\DoctrineExtensionBundle\ORM;

class EntityRepository extends \Doctrine\ORM\EntityRepository
{
    use EntityRepositoryFilterTrait;
}