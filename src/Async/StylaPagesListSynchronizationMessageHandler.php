<?php

namespace Styla\CmsIntegration\Async;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Styla\CmsIntegration\UseCase\StylaPagesSynchronizer;

class StylaPagesListSynchronizationMessageHandler implements MessageSubscriberInterface
{
    private StylaPagesSynchronizer $stylaPagesSynchronizer;
    private LoggerInterface $logger;

    public function __construct(StylaPagesSynchronizer $stylaPagesSynchronizer, LoggerInterface $logger)
    {
        $this->stylaPagesSynchronizer = $stylaPagesSynchronizer;
        $this->logger = $logger;
    }

    public function __invoke($message): void
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
