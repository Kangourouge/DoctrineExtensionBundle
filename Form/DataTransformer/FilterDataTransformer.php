<?php

namespace KRG\DoctrineExtensionBundle\Form\DataTransformer;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class FilterDataTransformer implements DataTransformerInterface
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var array */
    protected $fields;

    /**
     * FilterDataTransformer constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param array $fields
     */
    public function __construct(EntityManagerInterface $entityManager, array $fields)
    {
        $this->entityManager = $entityManager;
        $this->fields = $fields;
    }

    public function transform($data)
    {
        foreach($data as $name => &$value) {
            if ($this->fields[$name]['type'] === 'boolean') {
                $value = is_bool($value) ? (int) $value : '';
            }
            else if($value instanceof Collection) {
                $value = $value->toArray();
            }
            else if(is_array($value) && $this->fields[$name]['type'] === EntityType::class) {
                $value = $this->entityManager->getRepository($this->fields[$name]['type_options']['class'])->findBy(['id' => $value]);
            }
        }
        unset($value);

        return $data;
    }

    public function reverseTransform($data)
    {
        foreach($data as $name => &$value) {
            if ($this->fields[$name]['type'] === 'boolean' || $this->fields[$name]['type'] === CheckboxType::class) {
                $value = is_numeric($value) ? (bool)(int) $value : null;
            }
            else if($value instanceof Collection) {
                $value = $value->toArray();
            }
        }
        unset($value);

        return $data;
    }
}