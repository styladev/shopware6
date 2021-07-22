<?php

namespace Styla\CmsIntegration\Test\DataFixtures;

use Psr\Container\ContainerInterface;

interface TestDataFixturesInterface
{
    public function execute(ContainerInterface $container, ReferencesRegistry $referencesRegistry);
}
