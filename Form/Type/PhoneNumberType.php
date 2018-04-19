<?php

namespace KRG\DoctrineExtensionBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class PhoneNumberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('intlTel', TextType::class, array('label' => false))
                ->add('phoneNumber', HiddenType::class);

        $builder->addModelTransformer(new CallbackTransformer(
            function ($value) {
                if ($value === null) {
                    return array(
                        'intlTel' => null,
                        'phoneNumber' => null
                    );
                }
                return array(
                    'intlTel' => $value,
                    'phoneNumber' => $value
                );
            },
            function ($value) {
                if (!is_array($value) || !isset($value['phoneNumber']) || strlen($value['phoneNumber']) === 0) {
                    return null;
                }
                return $value['phoneNumber'];
            }
        ));
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