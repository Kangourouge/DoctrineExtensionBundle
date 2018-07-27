<?php

namespace KRG\DoctrineExtensionBundle\Request\ParamConverter;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class DoctrineParamConverter implements ParamConverterInterface
{
    /** @var ParamConverterInterface */
    protected $doctrineParamConverter;

    /** @var EntityManagerInterface */
    protected $entityManager;

    public function __construct(ParamConverterInterface $doctrineParamConverter, EntityManagerInterface $entityManager)
    {
        $this->doctrineParamConverter = $doctrineParamConverter;
        $this->entityManager = $entityManager;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        if ($this->isInterface($configuration->getClass())) {
            $class = $this->entityManager->getClassMetadata($configuration->getClass())->getName();
            $configuration->setClass($class);
        }

        return $this->doctrineParamConverter->apply($request, $configuration);
    }

    public function supports(ParamConverter $configuration)
    {
        return $this->doctrineParamConverter->supports($configuration) || $this->isInterface($configuration->getClass());
    }

    private function isInterface($class)
    {
        return preg_match('/^.+\\\Entity\\\.+Interface$/', $class);
    }
}
