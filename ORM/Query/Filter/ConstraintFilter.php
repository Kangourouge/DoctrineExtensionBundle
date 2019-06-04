<?php

namespace KRG\DoctrineExtensionBundle\ORM\Query\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use KRG\DoctrineExtensionBundle\DBAL\ConstraintEnumType;

class ConstraintFilter extends SQLFilter
{
    /** @var array */
    private $constraints = [];

    /** @var bool */
    private $idle = false;

    /**
     * @param array $constraints
     *
     * @return $this
     */
    public function setConstraints(array $constraints, bool $merge = false)
    {
        if (!$merge) {
            $this->constraints = $constraints;
        } elseif (count($constraints) > 0) {
            foreach($constraints as $className => $data) {
                if (!isset($this->constraints[$className])) {
                    $this->constraints[$className] = $data;
                } else {
                    $_data = &$this->constraints[$className];
                    if (isset($data[ConstraintEnumType::INCLUDE])) {
                        if (isset($_data[ConstraintEnumType::INCLUDE])) {
                            $_data[ConstraintEnumType::INCLUDE] = array_intersect($_data[ConstraintEnumType::INCLUDE], $data[ConstraintEnumType::INCLUDE]);
                        } else {
                            $_data[ConstraintEnumType::INCLUDE] = $data[ConstraintEnumType::INCLUDE];
                        }
                    }
                    if (isset($data[ConstraintEnumType::EXCLUDE])) {
                        if (isset($_data[ConstraintEnumType::EXCLUDE])) {
                            $_data[ConstraintEnumType::EXCLUDE] = array_merge($_data[ConstraintEnumType::EXCLUDE], $data[ConstraintEnumType::EXCLUDE]);
                        } else {
                            $_data[ConstraintEnumType::EXCLUDE] = $data[ConstraintEnumType::EXCLUDE];
                        }
                    }
                    unset($_data);
                }
            }
        }
        return $this;
    }

    /**
     * @param bool $idle
     *
     * @return $this
     */
    public function setIdle(bool $idle)
    {
        $this->idle = $idle;

        return $this;
    }

    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if (isset($this->constraints[$targetEntity->getName()])) {
            foreach ($this->constraints as $className => $data) {
                if ($targetEntity->getName() === $className) {
                    return $this->addClause($targetTableAlias, 'id', $data);
                }
            }
        } else {
            foreach ($this->constraints as $className => $data) {
                $associations = $targetEntity->getAssociationsByTargetClass($className);

                if (count($associations) === 0) {
                    continue;
                }

                $clauses = [];
                foreach ($associations as $association) {
                    $clause = $this->addClause($targetTableAlias, $association['targetToSourceKeyColumns']['id'], $data);

                    $clause = sprintf('%s.id IS NOT NULL OR %s', $targetTableAlias, $clause);

                    $clauses[] = $clause;
                }

                return implode(' AND ', $clauses);
            }
        }

        return '';
    }

    protected function addClause($alias, $field, array $data)
    {
        $type = null;
        if (isset($data[ConstraintEnumType::INCLUDE])) {
            $type = ConstraintEnumType::INCLUDE;
        }
        elseif (isset($data[ConstraintEnumType::EXCLUDE])) {
            $type = ConstraintEnumType::EXCLUDE;
        }
        else {
            return '';
        }

        $values = count($data[$type]) > 0 ? $data[$type] : [-1];

        return sprintf('%s.%s IS NULL OR %s.%s %s IN (%s)', $alias, $field, $alias, $field, $type === ConstraintEnumType::EXCLUDE ? 'NOT' : '', implode(',', $values), $alias);
    }
}