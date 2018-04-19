<?php

namespace KRG\DoctrineExtensionBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class EntityEditableType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $choices = array();
        if ($options['allow_select']) {
            $choices['select'] = $this->getLabel('Select an item', $options, 'select');

            $builder->add('_select', 'entity', array_merge($options['attr_select'], array(
                'class'    => $options['class'],
                'required' => true,
            )));
        }

        if ($options['allow_edit']) {
            $choices['edit'] = $this->getLabel('Edit the item', $options, 'edit');

            $builder->add('_edit', $options['entry_type'], array_merge($options['attr_edit'], array(
                'required' => true,
            )));
        }

        if ($options['allow_create']) {
            $choices['create'] = $this->getLabel('Create a new item', $options, 'create');

            $builder->add('_create', $options['entry_type'], array_merge($options['attr_create'], array(
                'required' => true,
            )));
        }

        if (count($choices)) {
            $builder->add('__action', ChoiceType::class, array_merge($options['attr_radio'], array(
                'required'    => $options['required'],
                'expanded'    => true,
                'empty_value' => $options['empty_value'],
                'empty_data'  => 'none',
                'choices'     => $choices,
            )));
        }

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            if ($form->getData() === null) {
                $form
                    ->remove('_edit')
                    ->remove('_edit_checkbox');
            }
        });

        $builder->addModelTransformer(new CallbackTransformer(function ($entity) use ($options) {
            $data = array();
            if ($options['allow_edit']) {
                $data['_edit'] = $entity;
                $data['__action'] = 'edit';
            }
            if ($options['allow_select']) {
                $data['_select'] = $entity;
                $data['__action'] = 'select';
            }
            if ($entity === null) {
                $data['__action'] = 'none';
            }

            return $data;
        },
            function ($data) {
                switch ($data['__action']) {
                    case 'create':
                        return $data['_create'];
                    case 'edit':
                        return $data['_edit'];
                    case 'select':
                        return $data['_select'];
                }

                return null;
            }
        ));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars = array_merge($view->vars, array(
            'attr_edit'                  => $options['attr_edit'],
            'attr_edit_checkbox'         => $options['attr_edit_checkbox'],
            'attr_create'                => $options['attr_create'],
            'attr_create_checkbox'       => $options['attr_create_checkbox'],
            'attr_select'                => $options['attr_select'],
            'attr_select_checkbox'       => $options['attr_select_checkbox'],
            'radio_actions'              => array_flip(array_keys($form->get('__action')->getConfig()->getOption('choices'))), // Numeric keys
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(array(
            'class',
            'entry_type',
        ));

        $resolver->setDefaults(array(
            'allow_edit'                 => false,
            'allow_select'               => false,
            'allow_create'               => false,
            'attr_create'                => array(),
            'attr_create_checkbox'       => array(),
            'attr_edit'                  => array(),
            'attr_edit_checkbox'         => array(),
            'attr_select'                => array(),
            'attr_select_checkbox'       => array(),
            'attr_radio'                 => array(),
            'required'                   => true,
            'empty_value'                => 'None',
        ));

        $resolver
            ->addAllowedTypes('class', 'string')
            ->addAllowedTypes('entry_type', array('string', FormTypeInterface::class))
            ->addAllowedTypes('allow_edit', 'bool')
            ->addAllowedTypes('allow_select', 'bool')
            ->addAllowedTypes('allow_create', 'bool')
            ->addAllowedTypes('attr_create', 'array')
            ->addAllowedTypes('attr_create_checkbox', 'array')
            ->addAllowedTypes('attr_edit', 'array')
            ->addAllowedTypes('attr_edit_checkbox', 'array')
            ->addAllowedTypes('attr_select', 'array')
            ->addAllowedTypes('attr_select_checkbox', 'array')
            ->addAllowedTypes('attr_radio', 'array')
            ->addAllowedTypes('empty_value', 'string');
    }

    public function getName()
    {
        return 'entity_editable';
    }

    /**
     * @param $label
     * @param array $options
     * @param $key
     * @return mixed
     */
    private function getLabel($label, array $options, $key)
    {
        $attr = $options['attr_'. $key];
        $attrCheckbox = $options['attr_'. $key .'_checkbox'];

        if ($attr && isset($attr['radio_label'])) {
            return $attr['radio_label'];
        }

        if ($attrCheckbox && isset($attrCheckbox['label'])) {
            return $attrCheckbox['label'];
        }

        return $label;
    }
}
