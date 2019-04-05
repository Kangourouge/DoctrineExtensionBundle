<?php

namespace KRG\DoctrineExtensionBundle\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use KRG\DoctrineExtensionBundle\Entity\Constraint\ConstraintInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Util\StringUtil;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConstraintCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $classes = call_user_func(sprintf('%s::getConstraintClasses', $options['class']));

        foreach ($classes as $idx => $class) {
            $builder->add(self::fqcnToBlockPrefix($class), CollectionType::class, [
                'entry_type' => ConstraintType::class,
                'entry_options' => ['class' => $class, 'label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ]);
        }

        $builder->addModelTransformer(new CallbackTransformer(
            function($constraints) {
                $result = [];
                if ($constraints instanceof Collection) {
                    /** @var ConstraintInterface $constraint */
                    foreach($constraints as $constraint) {
                        $class = self::fqcnToBlockPrefix($constraint->getEntityClass());
                        if (!isset($result[$class])) {
                            $result[$class] = [];
                        }
                        $result[$class][$constraint->getId()] = $constraint;
                    }
                }
                return $result;
            },
            function($constraints) {
                $result = [];
                if (is_array($constraints)) {
                    $result = call_user_func_array('array_merge', $constraints);
                }
                return new ArrayCollection($result);
            }
        ));

    }

    public static function fqcnToBlockPrefix($class)
    {
        $name = StringUtil::fqcnToBlockPrefix($class);
        return preg_replace('/_interface$/', '', $name);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('by_reference', false);
        $resolver->setRequired('class');
        $resolver->setAllowedTypes('class', 'string');
    }
}