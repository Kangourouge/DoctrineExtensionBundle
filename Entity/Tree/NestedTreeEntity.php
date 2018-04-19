<?php

namespace KRG\DoctrineExtensionBundle\Entity\Tree;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
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
     * Set lft
     *
     * @param integer $lft
     */
    public function setLft($lft)
    {
        $this->lft = $lft;

        return $this;
    }

    /**
     * Get lft
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * Set lvl
     *
     * @param integer $lvl
     */
    public function setLvl($lvl)
    {
        $this->lvl = $lvl;

        return $this;
    }

    /**
     * Get lvl
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * Set rgt
     *
     * @param integer $rgt
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;

        return $this;
    }

    /**
     * Get rgt
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * Set root
     */
    public function setRoot($root = null)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * Get root
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Is root
     */
    public function isRoot()
    {
        return $this->root === $this->id;
    }

    /**
     * Set parent
     */
    public function setParent(NestedTreeInterface $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add children
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
     */
    public function removeChild(NestedTreeInterface $child)
    {
        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
            $child->setParent(null);
        }
    }

    /**
     * Get children
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param ArrayCollection $children
     *
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
     *
     * @return bool
     */
    public function isParentOf(NestedTreeInterface $entity)
    {
        return $this->root === $entity->getRoot() && $this->lft < $entity->getLft() && $this->rgt > $entity->getRgt() && $this->lvl < $entity->getLvl();
    }

    /**
     * @param Collection $entities
     *
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
}
