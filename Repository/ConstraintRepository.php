<?php

namespace KRG\DoctrineExtensionBundle\Repository;

use KRG\DoctrineExtensionBundle\Entity\Constraint\EntityConstraintInterface;
use KRG\DoctrineExtensionBundle\ORM\EntityRepository;

class ConstraintRepository extends EntityRepository
{
    public function getConstraints(EntityConstraintInterface $entity = null, array $classes = [])
    {
        $queryBuilder = $this->createQueryBuilder('constraint')
                                ->select('constraint');
        if ($entity !== null) {
            $queryBuilder
                ->andWhere('constraint.foreignClass = :foreignClass')
                ->andWhere('constraint.foreignId = :foreignId')
                ->setParameter('foreignClass', get_class($entity))
                ->setParameter('foreignId', $entity->getId());
        }

        if (count($classes) > 0) {
            $queryBuilder->andWhere('constraint.entityClass in (:classes)')
                            ->setParameter('classes', $classes);
        }

        $data = $queryBuilder->getQuery()->getArrayResult();

        $constraints = [];
        foreach($data as $_data) {
            if (!isset($constraints[$_data['entityClass']])) {
                $constraints[$_data['entityClass']] = [];
            }
            if (!isset($constraints[$_data['entityClass']][$_data['type']])) {
                $constraints[$_data['entityClass']][$_data['type']] = [];
            }
            $constraints[$_data['entityClass']][$_data['type']][] = $_data['entityId'];
        }

        return $constraints;
    }
}