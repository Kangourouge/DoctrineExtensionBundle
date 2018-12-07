<?php

namespace KRG\DoctrineExtensionBundle\Form\Type;


use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MinMaxRangeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add($builder->getName().'_min', TextType::class, [
                'label'     => false,
                'required'  => false,
            ])
            ->add($builder->getName().'_max', TextType::class, [
                'label'     => false,
                'required'  => false,
            ]);
    }

    public function getBlockPrefix()
    {
        return 'min_max_range';
    }
}
