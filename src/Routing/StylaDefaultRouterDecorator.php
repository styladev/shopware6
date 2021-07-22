<?php

namespace Styla\CmsIntegration\Routing;

use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Direct inheritance because of the typehint in Shopware\Storefront\Framework\Routing\Router
 *
 * Reason to override:
 * - add possibility to decorate generator
 */
class StylaDefaultRouterDecorator extends Router
{
    private StylaUrlGeneratorFactory $generatorDecoratorFactory;

    public function getGenerator()
    {
        if (null === $this->generator) {
            $generator = parent::getGenerator();
            $decoratedGenerator = $this->generatorDecoratorFactory->create($generator);
            $this->generator = $decoratedGenerator;

            return $this->generator;
        }

        return parent::getGenerator();
    }

    public function setGeneratorDecoratorFactory(StylaUrlGeneratorFactory $generatorDecoratorFactory): void
    {
        $this->generatorDecoratorFactory = $generatorDecoratorFactory;
    }
}
