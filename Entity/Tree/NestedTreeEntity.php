<?php

namespace KRG\DoctrineExtensionBundle\Entity\Tree;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;

trait NestedTreeEntity
{
    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(type="integer")
     */
    protected $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(type="integer")
     */
    protected $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(type="integer")
     */
    protected $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(type="integer", nullable=true)
     * @var int
     */
    protected $root;

    /**
     * @param $lft integer
     * @return $this
     */
    public function setLft($lft)
    {
        $this->lft = $lft;

        return $this;
    }

    /**
     * @return integer
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * @param $lvl integer
     * @return $this
     */
    public function setLvl($lvl)
    {
        $this->lvl = $lvl;

        return $this;
    }

    /**
     * @return integer
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * @param $rgt
     * @return $this
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;

        return $this;
    }

    /**
     * @return integer
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * @param null $root
     * @return $this
     */
    public function setRoot($root = null)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * @return int
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @return bool
     */
    public function isRoot()
    {
        return $this->root === $this->id;
    }

    /**
     * @param NestedTreeInterface|null $parent
     * @return $this
     */
    public function setParent(NestedTreeInterface $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return NestedTreeInterface
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param NestedTreeInterface $child
     * @return $this
     */
    public function addChild(NestedTreeInterface $child)
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    /**
     * Remove children, annihilate the branch!
     *
     * @param NestedTreeInterface $child
     */
    public function removeChild(NestedTreeInterface $child)
    {
        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
            $child->setParent(null);
        }
    }

    /**
     * @return ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param ArrayCollection $children
     * @return $this
     */
    public function setChildren(ArrayCollection $children)
    {
        foreach ($this->children as $child) {
            if (!$children->contains($child)) {
                $this->removeChild($child);
            }
        }

        foreach ($children as $child) {
            $this->addChild($child);
        }

        return $this;
    }

    /**
     * @param NestedTreeInterface $entity
     * @return bool
     */
    public function isParentOf(NestedTreeInterface $entity)
    {
        return $this->root === $entity->getRoot() && $this->lft < $entity->getLft() && $this->rgt > $entity->getRgt() && $this->lvl < $entity->getLvl();
    }

    /**
     * @param Collection $entities
     * @return bool
     */
    public function isChildOf(Collection $entities)
    {
        foreach ($entities as $entity) {
            if ($this === $entity || $entity->isParentOf($this)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param null $entity
     * @return NestedTreeEntity|null
     */
    public function getRootParent($entity = null)
    {
        $entity = $entity === null ? $this : $entity;

        return $entity->getParent() ? $this->getRootParent($entity->getParent()) : $entity;
    }
}
