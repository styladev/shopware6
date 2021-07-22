<?php

namespace Styla\CmsIntegration\Entity\Specification;

use Styla\CmsIntegration\Entity\StylaIntegration\StylaPagesSynchronization;
use Styla\CmsIntegration\Utils\Specification\MinutesPassedAfterDateSpecification;
use Styla\CmsIntegration\Utils\Specification\SpecificationInterface;

class SyncWorksLongerThanSpecification implements SpecificationInterface
{
    private int $intervalInMinutes;

    public function __construct(int $intervalInMinutes)
    {
        $this->intervalInMinutes = $intervalInMinutes;
    }

    public static function create(int $intervalInMinutes): SyncWorksLongerThanSpecification
    {
        return new self($intervalInMinutes);
    }

    /**
     * @param StylaPagesSynchronization $value
     * {@inheritDoc}
     */
    public function isSatisfiedBy($value): bool
    {
        $startedAt = $value->getStartedAt();
        if (!$startedAt) {
            return false;
        }

        return MinutesPassedAfterDateSpecification::create($this->intervalInMinutes)->isSatisfiedBy($startedAt);
    }
}
