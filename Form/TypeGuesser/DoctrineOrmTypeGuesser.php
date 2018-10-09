<?php

namespace KRG\DoctrineExtensionBundle\Form\TypeGuesser;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use EMC\FileinputBundle\Entity\FileInterface;
use EMC\FileinputBundle\Form\Type\FileinputType;
use KRG\AddressBundle\Entity\AddressInterface;
use KRG\AddressBundle\Form\Type\AddressType;
use KRG\CmsBundle\Entity\PageInterface;
use KRG\CmsBundle\Entity\SeoInterface;
use KRG\CmsBundle\Form\Type\ContentType;
use KRG\CmsBundle\Form\Type\HtmlType;
use KRG\CmsBundle\Form\Type\RouteType;
use KRG\CmsBundle\Form\Type\SeoType;
use KRG\DoctrineExtensionBundle\DBAL\EnumType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Component\Form\Guess\ValueGuess;

class DoctrineOrmTypeGuesser implements FormTypeGuesserInterface
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var array */
    protected $enumTypes;

    /** @var array */
    private $cache;

    /**
     * DoctrineOrmTypeGuesser constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, array $enumTypes)
    {
        $this->entityManager = $entityManager;
        $this->enumTypes = $enumTypes;
        $this->cache = [];
    }

    public function guessType($class, $property)
    {
        $classMetadata = $this->getClassMetadata($class);
        if ($classMetadata instanceof ClassMetadata) {
            if ($classMetadata->hasAssociation($property)) {
                $targetClass = $classMetadata->getAssociationTargetClass($property);
                if (in_array(FileInterface::class, class_implements($targetClass))) {
                    $mapping = $classMetadata->getAssociationMapping($property);
                    $options = [
                        'legend' => true,
                        'multiple' => $mapping['type'] & ClassMetadataInfo::TO_MANY
                    ];
                    return new TypeGuess(FileinputType::class, $options, Guess::VERY_HIGH_CONFIDENCE);
                }

                if (in_array(AddressInterface::class, class_implements($targetClass))) {
                    return new TypeGuess(AddressType::class, [], Guess::VERY_HIGH_CONFIDENCE);
                }

                if (in_array(SeoInterface::class, class_implements($targetClass))) {
                    return new TypeGuess(SeoType::class, [], Guess::VERY_HIGH_CONFIDENCE);
                }
            }

            if ($classMetadata->hasField($property)) {
                $type = $classMetadata->getTypeOfField($property);
                if (isset($this->enumTypes[$type]) && in_array(EnumType::class, class_parents($this->enumTypes[$type]['class']))) {
                    $choices = call_user_func([$this->enumTypes[$type]['class'], 'getChoices']);
                    return new TypeGuess(ChoiceType::class, [
                        'choices' => $choices,
                        'choice_translation_domain' => $type
                    ], Guess::VERY_HIGH_CONFIDENCE);
                }
                else if ($type === 'html') {
                    $isFragment = !in_array(PageInterface::class, class_implements($class));
                    return new TypeGuess(HtmlType::class, ['fragment' => $isFragment], Guess::VERY_HIGH_CONFIDENCE);
                }
                else if ($type === 'locale') {
                    return new TypeGuess(LocaleType::class, [], Guess::VERY_HIGH_CONFIDENCE);
                }
                else if ($property === 'route') {
                    return new TypeGuess(RouteType::class, [], Guess::VERY_HIGH_CONFIDENCE);
                }
            }
        }
    }

    public function guessRequired($class, $property)
    {
        return null;
    }

    public function guessMaxLength($class, $property)
    {
        return null;
    }

    public function guessPattern($class, $property)
    {
        return null;
    }

    /**
     * @param $className
     *
     * @return ClassMetadata|null
     */
    private function getClassMetadata($className)
    {
        if (!isset($this->cache[$className])) {
            $this->cache[$className] = $this->entityManager->getClassMetadata($className);
        }
        return $this->cache[$className];
    }
}