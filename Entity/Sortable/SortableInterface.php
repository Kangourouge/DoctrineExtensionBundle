<?php

namespace KRG\DoctrineExtensionBundle\Entity\Sortable;

use Doctrine\Common\Collections\Collection;

interface SortableInterface
{
    /**
     * @return mixed
     */
    public function getPosition();

    /**
     * @param mixed $position
     *
     * @return SortableInterface
     */
    public function setPosition($position);
}
