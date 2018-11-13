<?php

namespace KRG\DoctrineExtensionBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use KRG\DoctrineExtensionBundle\Entity\Constraint\ConstraintInterface;
use KRG\DoctrineExtensionBundle\Entity\Constraint\EntityConstraintInterface;
use KRG\DoctrineExtensionBundle\Repository\ConstraintRepository;

class ConstraintListener implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [Events::postLoad];
    }

    public function postLoad(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof EntityConstraintInterface) {
            /** @var ConstraintRepository $repository */
            $repository = $event->getEntityManager()->getRepository(ConstraintInterface::class);
            $constraints = $repository->getConstraints($entity);
            $entity->setArrayConstraints($constraints);
        }
    }
}