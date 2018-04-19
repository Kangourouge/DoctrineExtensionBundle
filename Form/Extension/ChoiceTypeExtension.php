<?php

namespace KRG\DoctrineExtensionBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ChoiceTypeExtension
 * @package KRG\DoctrineExtensionBundle\Form\Type
 */
class ChoiceTypeExtension extends AbstractTypeExtension
{
    /**
     * @var array
     */
    protected $enumTypes;

    /**
     * ChoiceTypeExtension constructor.
     *
     * @param array $enumTypes
     */
    public function __construct(array $enumTypes)
    {
        $this->enumTypes = $enumTypes;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined(array('enum'))
            ->setNormalizer('choices', function (Options $options, $value) {
                if ($options->offsetExists('enum')) {
                    $enumType = $options->offsetGet('enum');
                    if (isset($this->enumTypes[$enumType]['class'])) {
                        return call_user_func(array($this->enumTypes[$enumType]['class'], 'getChoices'));
                    }
                }

                return $value;
            });
    }

    public function getExtendedType()
    {
        return ChoiceType::class;
    }
}
