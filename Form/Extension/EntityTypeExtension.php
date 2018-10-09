<?php

namespace KRG\DoctrineExtensionBundle\Form\Extension;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityTypeExtension extends AbstractTypeExtension
{
    /** @var ClassMetadataFactory */
    private $classMetadataFactory;

    /**
     * EntityTypeExtension constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->classMetadataFactory = $entityManager->getMetadataFactory();
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        if (in_array('class', $resolver->getDefinedOptions())) {
            $resolver->setNormalizer('class', function(OptionsResolver $resolver, $class) {
                return $this->classNormalizer($resolver, $class);
            });
        }

        $resolver->setNormalizer('data_class', function(OptionsResolver $resolver, $class) {
            return $this->classNormalizer($resolver, $class);
        });
    }

    /**
     * @param OptionsResolver $resolver
     * @param                 $class
     * @return string
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \ReflectionException
     */
    public function classNormalizer(OptionsResolver $resolver, $class)
    {
        if ($this->isInterface($class)) {
            if ($this->classMetadataFactory->hasMetadataFor($class)) {
                return $this->classMetadataFactory->getMetadataFor($class)->getName();
            }
        }

        return $class;
    }

    /**
     * @param $class
     * @return bool
     */
    private function isInterface($class)
    {
        return $class && interface_exists($class) && preg_match('/^.+\\\Entity\\\.+Interface$/', $class);
    }

    /**
     * @return string
     */
    public function getExtendedType()
    {
        return FormType::class;
    }
}
