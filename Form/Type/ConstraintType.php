<?php

namespace KRG\DoctrineExtensionBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use KRG\DoctrineExtensionBundle\Entity\Constraint\ConstraintInterface;
use KRG\DoctrineExtensionBundle\Form\DataTransformer\EntityDataTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
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
                ->add('entityId', EntityType::class, [
                    'class' => $options['class'],
                    'choice_value' => 'id'
                ]);

        $builder->get('entityId')->addModelTransformer(new EntityDataTransformer($this->entityManager, $options['class']));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', ConstraintInterface::class);
        $resolver->setRequired('class');
        $resolver->setNormalizer('empty_data', function(Options $options, $data){
            /** @var ConstraintInterface $constraint */
            $constraint = $this->entityManager->getClassMetadata(ConstraintInterface::class)->newInstance();
            $constraint->setEntityClass($options['class']);

            return $constraint;
        });
    }
}