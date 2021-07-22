<?php

namespace Styla\CmsIntegration\UseCase;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Styla\CmsIntegration\Async\StylaPagesListSynchronizationMessage;
use Styla\CmsIntegration\Configuration\ConfigurationInterface;
use Styla\CmsIntegration\Entity\Specification\StuckSyncSpecification;
use Styla\CmsIntegration\Entity\StylaIntegration\StylaSynchronizationDalHelper;
use Styla\CmsIntegration\Exception\SynchronizationInstanceStateIsInvalid;
use Styla\CmsIntegration\Exception\SynchronizationIsAlreadyRunning;
use Styla\CmsIntegration\Exception\UseCaseInteractorException;
use Styla\CmsIntegration\Styla\Synchronization\PagesListSynchronizationProcessor;
use Styla\CmsIntegration\Utils\Specification\SpecificationInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class StylaPagesSynchronizer
{
    private StylaSynchronizationDalHelper $stylaSynchronizationDalHelper;

    /**
     * @var SpecificationInterface|StuckSyncSpecification
     */
    private SpecificationInterface $stuckSynchronizationSpecification;
    private PagesListSynchronizationProcessor $pagesListSynchronizationProcessor;
    private MessageBusInterface $messageBus;
    private ConfigurationInterface $configuration;
    private LoggerInterface $logger;

    public function __construct(
        StylaSynchronizationDalHelper $stylaSynchronizationDalHelper,
        SpecificationInterface $stuckSynchronizationSpecification,
        PagesListSynchronizationProcessor $pagesListSynchronizationProcessor,
        MessageBusInterface $messageBus,
        ConfigurationInterface $configuration,
        LoggerInterface $logger
    ) {
        $this->stylaSynchronizationDalHelper = $stylaSynchronizationDalHelper;
        $this->stuckSynchronizationSpecification = $stuckSynchronizationSpecification;
        $this->pagesListSynchronizationProcessor = $pagesListSynchronizationProcessor;
        $this->logger = $logger;
        $this->configuration = $configuration;
        $this->messageBus = $messageBus;
    }

    /**
     * @throws SynchronizationIsAlreadyRunning
     * @throws UseCaseInteractorException
     */
    public function schedulePagesSynchronization(Context $context): void
    {
        try {
            $this->detectStuckSynchronizations($context);
            $this->doSchedulePagesSynchronization($context);
        } catch (SynchronizationIsAlreadyRunning $exception) {
            throw $exception;
        } catch (UseCaseInteractorException $exception) {
            $this->logger->error('Schedule Pages Synchronization failed', ['exception' => $exception]);

            throw $exception;
        } catch (\Throwable $exception) {
            $this->logger->error('Schedule Pages Synchronization failed', ['exception' => $exception]);

            throw new UseCaseInteractorException(
                'Schedule Pages Synchronization failed',
                UseCaseInteractorException::CODE_SYNCHRONIZATION_FAILED_TO_START,
                $exception
            );
        }
    }

    /**
     * @param Context $context
     *
     * @throws SynchronizationIsAlreadyRunning
     * @throws UseCaseInteractorException
     */
    public function scheduleAutomaticPagesSynchronizationIfNeeded(Context $context): void
    {
        try {
            $this->detectStuckSynchronizations($context);

            $interval = $this->configuration->getPageListSynchronizationInterval();

            $hasFinishedSynchronizationInInterval = $this->stylaSynchronizationDalHelper
                ->hasFinishedSynchronizationInInterval($interval, $context);
            if ($hasFinishedSynchronizationInInterval) {
                return;
            }

            $this->doSchedulePagesSynchronization($context);
        } catch (SynchronizationIsAlreadyRunning $exception) {
            $this->logger->warning('Synchronization schedule skipped, because it is still running');
        } catch (UseCaseInteractorException $exception) {
            $this->logger->error('Schedule Pages Synchronization failed', ['exception' => $exception]);

            throw $exception;
        } catch (\Throwable $exception) {
            $this->logger->error('Schedule Pages Synchronization failed', ['exception' => $exception]);

            throw new UseCaseInteractorException(
                'Schedule Pages Synchronization failed',
                UseCaseInteractorException::CODE_SYNCHRONIZATION_FAILED_TO_START,
                $exception
            );
        }
    }

    /**
     * @param Context $context
     *
     * @throws UseCaseInteractorException
     */
    private function doSchedulePagesSynchronization(Context $context)
    {
        $createdSynchronizationId = $this->stylaSynchronizationDalHelper->createSynchronization($context);

        $this->sendSynchronizationMessage($createdSynchronizationId, $context);

        $this->stylaSynchronizationDalHelper->tryMarkSynchronizationAsPending($createdSynchronizationId, $context);
    }

    /**
     * @param Context $context
     *
     * @throws SynchronizationIsAlreadyRunning
     * @throws \Throwable
     */
    private function detectStuckSynchronizations(Context $context)
    {
        $notFinishedSynchronization = $this->stylaSynchronizationDalHelper->getNotFinishedSynchronization($context);
        if ($notFinishedSynchronization) {
            if (!$this->stuckSynchronizationSpecification->isSatisfiedBy($notFinishedSynchronization)) {
                $this->logger->warning(
                    'Not finished synchronization found',
                    [
                        'id' => $notFinishedSynchronization->getId(),
                        'synchronization' => $notFinishedSynchronization
                    ]
                );

                throw new SynchronizationIsAlreadyRunning('Synchronization is currently running');
            }

            $this->logger->critical(
                'Stuck synchronization detected',
                ['id' => $notFinishedSynchronization->getId(), 'synchronization' => $notFinishedSynchronization]
            );

            $this->stylaSynchronizationDalHelper->markSynchronizationAsStuck($notFinishedSynchronization, $context);
        }
    }

    /**
     * @param string $synchronizationEntityId
     * @param Context $context
     *
     * @throws UseCaseInteractorException
     */
    private function sendSynchronizationMessage(string $synchronizationEntityId, Context $context)
    {
        try {
            $message = new StylaPagesListSynchronizationMessage($synchronizationEntityId, $context);

            $this->messageBus->dispatch($message);
        } catch (\Throwable $throwable) {
            $message = sprintf('Could not send message to start synchronization %s', $synchronizationEntityId);
            $this->logger->error($message, ['exception' => $throwable]);

            throw new UseCaseInteractorException(
                $message,
                UseCaseInteractorException::CODE_SYNCHRONIZATION_FAILED_TO_START
            );
        }
    }

    /**
     * @param string $synchronizationId
     * @param Context $context
     * @throws UseCaseInteractorException
     */
    public function synchronizeStylaPages(string $synchronizationId, Context $context): void
    {
        $synchronization = $this->stylaSynchronizationDalHelper->getSynchronizationById($synchronizationId, $context);
        if (!$synchronization) {
            $message = sprintf('Could not find synchronization %s', $synchronizationId);
            throw new UseCaseInteractorException(
                $message,
                UseCaseInteractorException::CODE_SYNCHRONIZATION_FAILED_TO_START
            );
        }

        try {
            $this->pagesListSynchronizationProcessor->synchronizePages($synchronization, $context);
        } catch (SynchronizationInstanceStateIsInvalid $exception) {
            $this->logger->error(
                sprintf('Synchronization %s skipped, reason: %s', $synchronizationId, $exception->getMessage()),
                [
                    'context' => $context
                ]
            );
            return;
        } catch (\Throwable $exception) {
            $this->logger->error(
                sprintf('Exception happened during the processing of the synchronization %s', $synchronizationId),
                [
                    'context' => $context
                ]
            );

            $this->stylaSynchronizationDalHelper->tryMarkSynchronizationAsFailed($synchronization, $context);

            throw new UseCaseInteractorException(
                'Pages synchronization process failed',
                UseCaseInteractorException::CODE_SYNCHRONIZATION_PROCESS_FAILED,
                $exception
            );
        }
    }
}
