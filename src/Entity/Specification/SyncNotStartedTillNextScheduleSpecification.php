<?php

namespace Styla\CmsIntegration\Entity\Specification;

use Styla\CmsIntegration\Configuration\ConfigurationInterface;
use Styla\CmsIntegration\Entity\StylaIntegration\StylaPagesSynchronization;
use Styla\CmsIntegration\Utils\Specification\MinutesPassedAfterDateSpecification;
use Styla\CmsIntegration\Utils\Specification\SpecificationInterface;

class SyncNotStartedTillNextScheduleSpecification implements SpecificationInterface
{
    private ConfigurationInterface $configuration;

    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param StylaPagesSynchronization $value
     * {@inheritDoc}
     */
    public function isSatisfiedBy($value): bool
    {
        if ($value->isStarted()) {
            return false;
        }

        $interval = $this->configuration->getPageListSynchronizationInterval();

        if (!$value->getCreatedAt()) {
            return true;
        }

        return MinutesPassedAfterDateSpecification::create($interval)->isSatisfiedBy($value->getCreatedAt());
    }
}
