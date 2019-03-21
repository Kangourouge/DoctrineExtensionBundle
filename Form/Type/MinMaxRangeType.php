<?php

namespace KRG\DoctrineExtensionBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class MinMaxRangeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $builder->getData();

        $builder
            ->add($builder->getName().'_min', TextType::class, [
                'label'     => false,
                'required'  => false,
                'data'      => (string)$data['min'],
            ])
            ->add($builder->getName().'_max', TextType::class, [
                'label'     => false,
                'required'  => false,
                'data'      => (string)$data['max'],
            ])
            ->add($builder->getName().'_from', TextType::class, [
                'label'     => false,
                'required'  => false,
                'data'      => (string)$data['min'],
            ])
            ->add($builder->getName().'_to', TextType::class, [
                'label'     => false,
                'required'  => false,
                'data'      => (string)$data['max'],
            ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'rebindMinMax']);
    }

    /**
     * Rebind form data if submitted request data is different from config
     */
    public function rebindMinMax(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        $data['price_min'] = $form->getData()['min'];
        $data['price_max'] = $form->getData()['max'];
        $data['price_from'] = $form->getData()['min'];
        $data['price_to'] = $form->getData()['max'];

        $event->setData($data);
    }

    public function getBlockPrefix()
    {
        return 'min_max_range';
    }
}
