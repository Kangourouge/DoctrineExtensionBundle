<?php

namespace KRG\DoctrineExtensionBundle\Form\DataTransformer;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class EntityDataTransformer implements DataTransformerInterface
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var string */
    protected $class;

    /**
     * FilterDataTransformer constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param string $class
     */
    public function __construct(EntityManagerInterface $entityManager, string $class)
    {
        $this->entityManager = $entityManager;
        $this->class = $class;
    }

    public function transform($data)
    {
        if ($data === null) {
            return null;
        }

        return $this->entityManager->find($this->class, $data);
    }

    public function reverseTransform($data)
    {
        if ($data === null) {
            return null;
        }

        return $data->getId();
    }
}