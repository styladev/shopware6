<?php

namespace Styla\CmsIntegration\Async;

use Psr\Log\LoggerInterface;
use Styla\CmsIntegration\Async\StylaPagesListSynchronizationMessage;
use Styla\CmsIntegration\UseCase\StylaPagesSynchronizer;

class StylaPagesListSynchronizationMessageHandler {
    private StylaPagesSynchronizer $stylaPagesSynchronizer;
    private LoggerInterface $logger;

    public function __construct(StylaPagesSynchronizer $stylaPagesSynchronizer, LoggerInterface $logger)
    {
        $this->stylaPagesSynchronizer = $stylaPagesSynchronizer;
        $this->logger = $logger;
    }

    public function __invoke(StylaPagesListSynchronizationMessage $message): void
    {
        try {
            if (!$message instanceof StylaPagesListSynchronizationMessage) {
                throw new \RuntimeException(
                    sprintf(
                        'Unexpected message type, expected %s, got %s',
                        StylaPagesListSynchronizationMessage::class,
                        is_object($message) ? get_class($message) : gettype($message)
                    )
                );
            }

            $this->stylaPagesSynchronizer
                ->synchronizeStylaPages($message->getStylaSynchronizationId(), $message->getContext());
        } catch (\Throwable $exception) {
            // Should not trigger any exceptions to avoid requeue
            $this->logger->error(
                'Failed to process styla pages synchronization message',
                [
                    'exception' => $exception
                ]
            );
        }
    }

    public static function getHandledMessages(): iterable
    {
        return [StylaPagesListSynchronizationMessage::class];
    }
}
