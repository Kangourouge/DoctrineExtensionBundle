<?php

namespace KRG\DoctrineExtensionBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use KRG\DoctrineExtensionBundle\Entity\Constraint\ConstraintInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConstraintType extends AbstractType
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * ConstraintType constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('type')
                ->add('entity', EntityType::class, [
                    'class' => $options['class']
                ]);

        $builder->addModelTransformer(new CallbackTransformer(
            function($value) {
                if ($value instanceof ConstraintInterface) {
                    $value->setEntity($this->entityManager->find($value->getEntityClass(), $value->getEntityId()));
                }
                return $value;
            },
            function($value) {
                return $value;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', ConstraintInterface::class);
        $resolver->setRequired('class');
    }
}