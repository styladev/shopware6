<?php

namespace Styla\CmsIntegration\Test\DataFixtures;

interface DependentTestDataFixtureInterface extends TestDataFixturesInterface
{
    public function getDependenciesList(): array;
}
