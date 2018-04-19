<?php

namespace KRG\DoctrineExtensionBundle\Session;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class EntitySessionStorage
 * @package KRG\DoctrineExtensionBundle\Session
 * @todo use MetadataClassName to create entity instances
 */
class EntitySessionStorage
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var string
     */
    private $className;

    function __construct(EntityManagerInterface $entityManager, SessionInterface $session, $className)
    {
        $this->entityManager = $entityManager;
        $this->session = $session;
        $this->className = $className;
    }

    public function get($key)
    {
        return $this->find($key) ?: new $this->className;
    }

    public function find($key)
    {
        $key = sprintf('__%s/%s', $this->className, $key);
        $entity = $this->session->get($key);
        if ($entity !== null) {
            return $this->entityManager->merge($entity);
        }

        return null;
    }

    public function set($key, $entity)
    {

        $reflection = new \ReflectionClass($this->className);
        if (!$reflection->isInstance($entity)) {
            throw new \InvalidArgumentException();
        }

        $key = sprintf('__%s/%s', $this->className, $key);
        $this->session->set($key, $entity);
        $this->entityManager->detach($entity);

        return $this;
    }

    public function clear()
    {
        $data = $this->session->all();
        foreach($data as $key => $_) {
            if (substr($key, 0, strlen($this->className) + 3) === sprintf('__%s/', $this->className)) {
                $this->session->remove($key);
            }
        }
    }

    public function has($key)
    {
        $key = sprintf('__%s/%s', $this->className, $key);
        return $this->session->has($key);
    }

    public function remove($key)
    {
        $key = sprintf('__%s/%s', $this->className, $key);
        $this->session->remove($key);
    }
}
