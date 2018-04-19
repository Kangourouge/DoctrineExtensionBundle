<?php

namespace KRG\DoctrineExtensionBundle\Form\Extension;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityTypeExtension extends AbstractTypeExtension
{
    /**
     * @var ClassMetadataFactory
     */
    private $classMetadataFactory;

    /**
     * EntityTypeExtension constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->classMetadataFactory = $entityManager->getMetadataFactory();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setNormalizer('class', function (OptionsResolver $resolver, $class) {
            return $this->isInterface($class) && $this->classMetadataFactory->hasMetadataFor($class) ? $this->classMetadataFactory->getMetadataFor($class)->getName() : $class;
        });
    }

    private function isInterface($class)
    {
        return preg_match('/^.+\\\Entity\\\.+Interface$/', $class);
    }

    public function getExtendedType()
    {
        return EntityType::class;
    }
}
