<?php

namespace KRG\DoctrineExtensionBundle\DBAL;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

abstract class EnumType extends Type
{
    public static $values = array();

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $values = array_map(function($val) { return "'".$val."'"; }, static::$values);
        sort($values);
        
        return "ENUM(".implode(", ", $values).") COMMENT '(DC2Type:".$this->getName().")'";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ( null === $value ) {
            return null;
        }
        if (!in_array($value, static::$values)) {
            throw new \InvalidArgumentException("Invalid '".$this->getName()."' key.");
        }
        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null !== $value && !in_array($value, static::$values)) {
            throw new \InvalidArgumentException("Invalid '".$this->getName()."' key.");
        }
        return $value;
    }

    public static function getChoices(array $values=null) {
        $values = $values !== null ? $values : static::$values;
        return array_combine(
            array_map(
                function($choice){
                    return ucwords(preg_replace('/[-_\.]/', ' ', $choice));
                },
                $values
            ),
            $values
        );
    }
}
