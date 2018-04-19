<?php

namespace KRG\DoctrineExtensionBundle\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class DoctrineParamConverter extends \Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter
{
    public function apply(Request $request, ParamConverter $configuration)
    {
        if ($this->isInterface($configuration->getClass())) {
            $class = $this->registry->getManager()->getClassMetadata($configuration->getClass())->getName();
            $configuration->setClass($class);
        }
        return parent::apply($request, $configuration);
    }

    public function supports(ParamConverter $configuration)
    {
        return parent::supports($configuration) || $this->isInterface($configuration->getClass());
    }

    private function isInterface($class)
    {
        return preg_match('/^.+\\\Entity\\\.+Interface$/', $class);
    }
}
