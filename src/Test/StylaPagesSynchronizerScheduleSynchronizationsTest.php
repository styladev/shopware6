<?php

namespace Styla\CmsIntegration\Test;

use Doctrine\DBAL\Types\Types;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;
use Styla\CmsIntegration\Configuration\ConfigurationFactory;
use Styla\CmsIntegration\Entity\StylaIntegration\StylaPagesSynchronization;
use Styla\CmsIntegration\Entity\StylaIntegration\StylaSynchronizationDalHelper;
use Styla\CmsIntegration\Exception\SynchronizationIsAlreadyRunning;
use Styla\CmsIntegration\Test\Constraint\StylaSynchronizationMatchConstraint;
use Styla\CmsIntegration\Test\Constraint\StylaSynchronizationsListMatchConstraint;

class StylaPagesSynchronizerScheduleSynchronizationsTest extends AbstractStylaPagesSynchronizationTestCase
{
    public function testStylaPagesSynchronizationScheduled()
    {
        $context = Context::createDefaultContext();
        $this->stylaPagesSynchronizer->schedulePagesSynchronization($context);

        $synchronization = $this->getSingleSynchronization($context);

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $expectedSynchronizationMatchConstraint = new StylaSynchronizationMatchConstraint(
            true,
            StylaPagesSynchronization::STATUS_PENDING,
            null,
            null,
            $now,
            $now
        );
        self::assertThat($synchronization, $expectedSynchronizationMatchConstraint);
    }

    public function testStylaPagesSynchronizationWasNotScheduleIfWeHaveAnotherPendingSynchronization()
    {
        $context = Context::createDefaultContext();
        $this->stylaPagesSynchronizer->schedulePagesSynchronization($context);

        $this->expectException(SynchronizationIsAlreadyRunning::class);
        $this->expectExceptionMessage('Synchronization is currently running');
        $this->stylaPagesSynchronizer->schedulePagesSynchronization($context);
    }

    public function testStylaPagesSynchronizationWasNotScheduleIfWeHaveAnotherInProgressSynchronization()
    {
        $context = Context::createDefaultContext();
        $helper = $this->getContainer()->get(StylaSynchronizationDalHelper::class);

        $synchronizationId = $helper->createSynchronization($context);
        $synchronization = $helper->getSynchronizationById($synchronizationId, $context);
        $helper->markSynchronizationAsInProgress($synchronization, $context);

        $this->expectException(SynchronizationIsAlreadyRunning::class);
        $this->expectExceptionMessage('Synchronization is currently running');
        $this->stylaPagesSynchronizer->schedulePagesSynchronization($context);
    }

    public function testStylaPagesSynchronizationScheduleIfPreviousSynchronizationWasNotStartedTillNextSchedule()
    {
        $context = Context::createDefaultContext();

        $createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        // Default schedule interval is 10 minutes
        $createdAt->sub(new \DateInterval('PT15M'));
        $this->createPendingStuckSynchronizationWithCustomCreatedAt($createdAt, $context);

        $this->stylaPagesSynchronizer->schedulePagesSynchronization($context);

        $synchronizations = $this->getOrderedSynchronizationsList($context);
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $constraint = new StylaSynchronizationsListMatchConstraint(
            [
                new StylaSynchronizationMatchConstraint(
                    false,
                    StylaPagesSynchronization::STATUS_STUCK,
                    null,
                    $now,
                    $createdAt,
                    $now
                ),
                new StylaSynchronizationMatchConstraint(
                    true,
                    StylaPagesSynchronization::STATUS_PENDING,
                    null,
                    null,
                    $now,
                    $now
                )
            ]
        );
        self::assertThat($synchronizations->getElements(), $constraint);
    }

    public function testStylaPagesSynchronizationScheduleIfPreviousSynchronizationWasNotFinishedInMaxExecutionTime()
    {
        $context = Context::createDefaultContext();
        // 10 minutes for now
        $maxExecutionTime = $this->getContainer()
            ->getParameter('styla.cms_integration.page_list.synchronization.maximum_execution_time');
        $maxExecutionTime += 1;

        $startedAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $startedAt->sub(new \DateInterval(sprintf('PT%sM', $maxExecutionTime)));

        $this->createInProgressSynchronizationWithCustomStartedDate($startedAt, $context);

        $this->stylaPagesSynchronizer->schedulePagesSynchronization($context);

        $synchronizations = $this->getOrderedSynchronizationsList($context);
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $constraint = new StylaSynchronizationsListMatchConstraint(
            [
                new StylaSynchronizationMatchConstraint(
                    false,
                    StylaPagesSynchronization::STATUS_STUCK,
                    $startedAt,
                    $now,
                    $now,
                    $now
                ),
                new StylaSynchronizationMatchConstraint(
                    true,
                    StylaPagesSynchronization::STATUS_PENDING,
                    null,
                    null,
                    $now,
                    $now
                )
            ]
        );
        self::assertThat($synchronizations->getElements(), $constraint);
    }

    public function testScheduleAutomaticPagesSynchronizationIfNeededWorks()
    {
        $context = Context::createDefaultContext();
        $this->stylaPagesSynchronizer->scheduleAutomaticPagesSynchronizationIfNeeded($context);

        $synchronization = $this->getSingleSynchronization($context);

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $expectedSynchronizationMatchConstraint = new StylaSynchronizationMatchConstraint(
            true,
            StylaPagesSynchronization::STATUS_PENDING,
            null,
            null,
            $now,
            $now
        );
        self::assertThat($synchronization, $expectedSynchronizationMatchConstraint);
    }

    public function testAutomaticPagesSynchronizationWillNotBeScheduledIfThereIsAnotherPendingSynchronization()
    {
        $context = Context::createDefaultContext();
        $this->stylaPagesSynchronizer->schedulePagesSynchronization($context);
        $synchronizations = $this->getOrderedSynchronizationsList($context);
        self::assertCount(1, $synchronizations);

        $this->stylaPagesSynchronizer->scheduleAutomaticPagesSynchronizationIfNeeded($context);
        $synchronizations = $this->getOrderedSynchronizationsList($context);
        self::assertCount(1, $synchronizations);
    }

    public function testAutomaticPagesSynchronizationWillNotBeScheduledIfThereIsAnotherRunningSynchronization()
    {
        $context = Context::createDefaultContext();
        $helper = $this->getContainer()->get(StylaSynchronizationDalHelper::class);

        $synchronizationId = $helper->createSynchronization($context);
        $synchronization = $helper->getSynchronizationById($synchronizationId, $context);
        $helper->markSynchronizationAsInProgress($synchronization, $context);

        $this->stylaPagesSynchronizer->scheduleAutomaticPagesSynchronizationIfNeeded($context);
        $synchronizations = $this->getOrderedSynchronizationsList($context);
        self::assertCount(1, $synchronizations);
    }

    public function testAutomaticPagesSynchronizationWillBeScheduledIfThereIsNotStartedStuckSynchronization()
    {
        $context = Context::createDefaultContext();

        $createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        // Default schedule interval is 10 minutes
        $createdAt->sub(new \DateInterval('PT15M'));
        $this->createPendingStuckSynchronizationWithCustomCreatedAt($createdAt, $context);

        $this->stylaPagesSynchronizer->scheduleAutomaticPagesSynchronizationIfNeeded($context);

        $synchronizations = $this->getOrderedSynchronizationsList($context);
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $constraint = new StylaSynchronizationsListMatchConstraint(
            [
                new StylaSynchronizationMatchConstraint(
                    false,
                    StylaPagesSynchronization::STATUS_STUCK,
                    null,
                    $now,
                    $createdAt,
                    $now
                ),
                new StylaSynchronizationMatchConstraint(
                    true,
                    StylaPagesSynchronization::STATUS_PENDING,
                    null,
                    null,
                    $now,
                    $now
                )
            ]
        );
        self::assertThat($synchronizations->getElements(), $constraint);
    }

    public function testAutomaticPagesSynchronizationWillBeScheduledIfThereIsRunningStuckSynchronization()
    {
        $context = Context::createDefaultContext();

        // 10 minutes for now
        $maxExecutionTime = $this->getContainer()
            ->getParameter('styla.cms_integration.page_list.synchronization.maximum_execution_time');
        $maxExecutionTime += 1;

        $startedAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $startedAt->sub(new \DateInterval(sprintf('PT%sM', $maxExecutionTime)));

        $this->createInProgressSynchronizationWithCustomStartedDate($startedAt, $context);

        $this->stylaPagesSynchronizer->scheduleAutomaticPagesSynchronizationIfNeeded($context);

        $synchronizations = $this->getOrderedSynchronizationsList($context);
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $constraint = new StylaSynchronizationsListMatchConstraint(
            [
                new StylaSynchronizationMatchConstraint(
                    false,
                    StylaPagesSynchronization::STATUS_STUCK,
                    $startedAt,
                    $now,
                    $now,
                    $now
                ),
                new StylaSynchronizationMatchConstraint(
                    true,
                    StylaPagesSynchronization::STATUS_PENDING,
                    null,
                    null,
                    $now,
                    $now
                )
            ]
        );
        self::assertThat($synchronizations->getElements(), $constraint);
    }

    public function testAutomaticPagesSynchronizationWillBeScheduledIfThereIsSyncFinishedEarlierThenScheduleInterval()
    {
        $context = Context::createDefaultContext();

        $finishedAt = new \DateTime('now', new \DateTimeZone('UTC'));

        $executionInterval = $this->systemConfigService
            ->get(ConfigurationFactory::PAGES_LIST_SYNCHRONIZATION_INTERVAL_CONFIG_KEY);
        $executionInterval +=1;
        $finishedAt->sub(new \DateInterval(sprintf('PT%sM', $executionInterval)));

        $this->createSuccessSynchronizationWithCustomFinishDate($finishedAt, $context);

        $this->stylaPagesSynchronizer->scheduleAutomaticPagesSynchronizationIfNeeded($context);

        $synchronizations = $this->getOrderedSynchronizationsList($context);
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $constraint = new StylaSynchronizationsListMatchConstraint(
            [
                new StylaSynchronizationMatchConstraint(
                    false,
                    StylaPagesSynchronization::STATUS_SUCCESS,
                    $now,
                    $finishedAt,
                    $now,
                    $now
                ),
                new StylaSynchronizationMatchConstraint(
                    true,
                    StylaPagesSynchronization::STATUS_PENDING,
                    null,
                    null,
                    $now,
                    $now
                )
            ]
        );
        self::assertThat($synchronizations->getElements(), $constraint);
    }

    public function testAutomaticPagesSynchronizationWillNotBeScheduledIfThereIsSyncFinishedLaterThenScheduleInterval()
    {
        $context = Context::createDefaultContext();

        $finishedAt = new \DateTime('now', new \DateTimeZone('UTC'));

        $executionInterval = $this->systemConfigService
            ->get(ConfigurationFactory::PAGES_LIST_SYNCHRONIZATION_INTERVAL_CONFIG_KEY);
        $executionInterval -=1;
        $finishedAt->sub(new \DateInterval(sprintf('PT%sM', $executionInterval)));

        $this->createSuccessSynchronizationWithCustomFinishDate($finishedAt, $context);

        $this->stylaPagesSynchronizer->scheduleAutomaticPagesSynchronizationIfNeeded($context);

        $synchronizations = $this->getOrderedSynchronizationsList($context);
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $constraint = new StylaSynchronizationsListMatchConstraint(
            [
                new StylaSynchronizationMatchConstraint(
                    false,
                    StylaPagesSynchronization::STATUS_SUCCESS,
                    $now,
                    $finishedAt,
                    $now,
                    $now
                )
            ]
        );
        self::assertThat($synchronizations->getElements(), $constraint);
    }

    private function createPendingStuckSynchronizationWithCustomCreatedAt(\DateTime $createdAt, Context $context)
    {
        $this->stylaPagesSynchronizer->schedulePagesSynchronization($context);

        $synchronization = $this->getSingleSynchronization($context);

        $this->connection->executeStatement(
            'UPDATE styla_cms_pages_synchronization SET created_at=:createdAt WHERE id = :id',
            [
                'createdAt' => $createdAt,
                'id'=> Uuid::fromHexToBytes($synchronization->getId())
            ],
            [
                'createdAt' => Types::DATETIME_MUTABLE,
                'id'=> Types::STRING
            ]
        );
    }

    private function createInProgressSynchronizationWithCustomStartedDate(\DateTime $startedAt, Context $context)
    {
        $helper = $this->getContainer()->get(StylaSynchronizationDalHelper::class);

        $synchronizationId = $helper->createSynchronization($context);
        $synchronization = $helper->getSynchronizationById($synchronizationId, $context);
        $helper->markSynchronizationAsInProgress($synchronization, $context);

        $this->stylaSynchronizationRepository->update(
            [
                [
                    'id' => $synchronization->getId(),
                    'startedAt' => $startedAt
                ]
            ],
            $context
        );
    }

    private function createSuccessSynchronizationWithCustomFinishDate(\DateTime $finishedAt, Context $context)
    {
        $helper = $this->getContainer()->get(StylaSynchronizationDalHelper::class);

        $synchronizationId = $helper->createSynchronization($context);
        $synchronization = $helper->getSynchronizationById($synchronizationId, $context);
        $helper->markSynchronizationAsInProgress($synchronization, $context);
        $helper->markSynchronizationAsSuccess($synchronization, $context);

        $this->stylaSynchronizationRepository->update(
            [
                [
                    'id' => $synchronization->getId(),
                    'finishedAt' => $finishedAt
                ]
            ],
            $context
        );
    }
}
