<?php

namespace KRG\DoctrineExtensionBundle\ORM;

use Doctrine\ORM\EntityManagerInterface;
use KRG\DoctrineExtensionBundle\Entity\Constraint\EntityConstraintInterface;
use KRG\DoctrineExtensionBundle\ORM\Query\Filter\ConstraintFilter;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class EntityManagerDecorator extends \Doctrine\ORM\Decorator\EntityManagerDecorator
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage)
    {
        parent::__construct($entityManager);
        $this->tokenStorage = $tokenStorage;
    }

    public function setConstraints(array $constraints)
    {
        /** @var ConstraintFilter $filter */
        $this->getFilters()->getFilter('constraint_filter')->setConstraints($constraints, true);
    }

    public function getRepository($className)
    {
        return $this->getConfiguration()->getRepositoryFactory()->getRepository($this, $className);
    }

    public function find($entityName, $id, $lockMode = null, $lockVersion = null)
    {
        $this->addUserConstraints();
        return parent::find($entityName, $id, $lockMode, $lockVersion);
    }

    public function createQuery($dql = '')
    {
        $this->addUserConstraints();

        $query = parent::createQuery($dql);

        return $query;
    }

    public function createQueryBuilder()
    {
        return new QueryBuilder($this);
    }

    public function addUserConstraints()
    {
        $token = $this->tokenStorage->getToken();
        if ($token instanceof TokenInterface && ($user = $token->getUser()) instanceof EntityConstraintInterface) {
            $this->setConstraints($user->getArrayConstraints());
        }
    }
}