<?php

namespace Styla\CmsIntegration\Entity\Specification;

use Styla\CmsIntegration\Utils\Specification\SpecificationInterface;

class StuckSyncSpecification implements SpecificationInterface
{
    /**
     * @var SpecificationInterface|SyncNotStartedTillNextScheduleSpecification
     */
    private SpecificationInterface $syncNotStartedTillNextSchedule;
    private int $maximumSyncProcessExecutionTime;

    public function __construct(
        SpecificationInterface $syncNotStartedTillNextSchedule,
        int $maximumSyncProcessExecutionTime
    ) {
        $this->syncNotStartedTillNextSchedule = $syncNotStartedTillNextSchedule;
        $this->maximumSyncProcessExecutionTime = $maximumSyncProcessExecutionTime;
    }

    public function isSatisfiedBy($value): bool
    {
        if ($this->syncNotStartedTillNextSchedule->isSatisfiedBy($value)) {
            return true;
        }

        return SyncWorksLongerThanSpecification::create($this->maximumSyncProcessExecutionTime)
            ->isSatisfiedBy($value);
    }
}
