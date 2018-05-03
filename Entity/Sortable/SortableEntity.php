<?php

namespace KRG\DoctrineExtensionBundle\Entity\Sortable;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

trait SortableEntity
{
    /**
     * @ORM\Column(type="integer")
     */
    protected $position;

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param mixed $position
     *
     * @return SortableInterface
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }
}
