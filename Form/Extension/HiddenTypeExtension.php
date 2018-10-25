<?php

namespace KRG\DoctrineExtensionBundle\Form\Extension;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HiddenTypeExtension extends AbstractTypeExtension
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /**
     * HiddenTypeExtension constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (is_string($options['data_class'])) {
            $className = $options['data_class'];
            $builder->addModelTransformer(new CallbackTransformer(
                function ($value) {
                    return $value;
                },
                function ($value) use ($className) {
                    if (strlen($value) === 0) {
                        return null;
                    }
                    return $this->entityManager->find($className, $value);
                }
            ));
        }
    }

    public function getExtendedType()
    {
        return HiddenType::class;
    }
}