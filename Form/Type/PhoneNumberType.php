<?php

namespace KRG\DoctrineExtensionBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhoneNumberType extends AbstractType
{
    /** @var string */
    private $locale;

    public function __construct(string $locale)
    {
        $this->locale = $locale;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('intlTel', TextType::class, ['label' => false])
            ->add('phoneNumber', HiddenType::class);

        $builder->addModelTransformer(new CallbackTransformer(
            function ($value) {
                if ($value === null) {
                    return [
                        'intlTel'     => null,
                        'phoneNumber' => null
                    ];
                }

                return [
                    'intlTel'     => $value,
                    'phoneNumber' => $value
                ];
            },
            function ($value) {
                if (!is_array($value) || !isset($value['phoneNumber']) || strlen($value['phoneNumber']) === 0) {
                    return null;
                }

                return $value['phoneNumber'];
            }
        ));
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);

        $pluginOptions = [];
        foreach ($options['options'] as $option => $value) {
            if (is_array($value) || is_bool($value)) {
                $value = json_encode($value);
            } elseif (is_string($value)) {
                $value = '"'.$value.'"';
            }

            $pluginOptions[$option] = $value;
        }

        $view->vars['plugin_options'] = $pluginOptions;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'options' => [],
        ]);
    }

    public function getBlockPrefix()
    {
        return $this->getName();
    }

    public function getName()
	{
		return 'phone_number';
	}
}
