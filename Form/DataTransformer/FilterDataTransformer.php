<?php

namespace KRG\DoctrineExtensionBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class FilterDataTransformer implements DataTransformerInterface
{
    /** @var array */
    protected $fields;

    /**
     * FilterDataTransformer constructor.
     *
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    public function transform($data)
    {
        foreach($data as $name => &$value) {
            if ($this->fields[$name]['type'] === 'boolean') {
                $value = is_bool($value) ? (int) $value : '';
            }
        }
        unset($value);

        return $data;
    }

    public function reverseTransform($data)
    {
        foreach($data as $name => &$value) {
            if ($this->fields[$name]['type'] === 'boolean') {
                $value = is_numeric($value) ? (bool)(int) $value : null;
            }
        }
        unset($value);

        return $data;
    }
}