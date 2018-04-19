<?php

namespace KRG\DoctrineExtensionBundle\Entity\Tree;

use Doctrine\Common\Collections\Collection;

interface NestedTreeInterface
{
    /**
     * @param $lft int
     *
     * @return NestedTreeInterface
     */
    public function setLft($lft);


    /**
     * @return int
     */
    public function getLft();

    /**
     * @param $lvl int
     *
     * @return NestedTreeInterface
     */
    public function setLvl($lvl);

    /**
     * @return int
     */
    public function getLvl();

    /**
     * @param $rgt int
     *
     * @return NestedTreeInterface
     */
    public function setRgt($rgt);

    /**
     * @return int
     */
    public function getRgt();

    /**
     * @param $root int
     *
     * @return NestedTreeInterface
     */
    public function setRoot($root = null);

    /**
     * @return int
     */
    public function getRoot();

    /**
     * @return bool
     */
    public function isRoot();

    /**
     * @param $parent NestedTreeInterface|null
     *
     * @return NestedTreeInterface
     */
    public function setParent(NestedTreeInterface $parent = null);

    /**
     * @return NestedTreeInterface|null
     */
    public function getParent();

    /**
     * @param NestedTreeInterface $child
     *
     * @return NestedTreeInterface
     */
    public function addChild(NestedTreeInterface $child);

    /**
     * @param NestedTreeInterface $child
     *
     * @return NestedTreeInterface
     */
    public function removeChild(NestedTreeInterface $child);

    /**
     * @return Collection
     */
    public function getChildren();

    /**
     * @param NestedTreeInterface $entity
     *
     * @return bool
     */
    public function isParentOf(NestedTreeInterface $entity);

    /**
     * @param Collection $entities
     *
     * @return bool
     */
    public function isChildOf(Collection $entities);
}
