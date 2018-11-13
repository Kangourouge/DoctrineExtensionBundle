<?php

namespace KRG\DoctrineExtensionBundle\DBAL;

class ConstraintEnumType extends EnumType
{
    const
        INCLUDE = 'include',
        EXCLUDE = 'exclude'
    ;

    public static $values = [self::INCLUDE, self::EXCLUDE];

    public function getName()
    {
        return 'constraint_enum';
    }
}
