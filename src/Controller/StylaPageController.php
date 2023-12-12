<?php

namespace Styla\CmsIntegration\Controller;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Styla\CmsIntegration\Exception\PageNotFoundException;
use Styla\CmsIntegration\Exception\SynchronizationIsAlreadyRunning;
use Styla\CmsIntegration\Exception\UseCaseInteractorException;
use Styla\CmsIntegration\UseCase\StylaPagesInteractor;
use Styla\CmsIntegration\UseCase\StylaPagesSynchronizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"api"}})
 * @Route(
 *     "api/styla/page"
 * )
 */
class StylaPageController
{
    private StylaPagesInteractor $stylaPagesInteractor;
    private StylaPagesSynchronizer $stylaPagesSynchronizer;
    private EntityRepository $repository;
    private LoggerInterface $logger;

    public function __construct(
        StylaPagesInteractor $interactor,
        EntityRepository $repository,
        StylaPagesSynchronizer $stylaPagesSynchronizer,
        LoggerInterface $logger
    ) {
        $this->stylaPagesInteractor = $interactor;
        $this->repository = $repository;
        $this->stylaPagesSynchronizer = $stylaPagesSynchronizer;
        $this->logger = $logger;
    }

     /**
     * @Route(
     *     "/_action/schedule-pages-synchronization",
     *     name="api.styla.page.schedule-pages-synchronization",
     *     methods={"POST"},
     *     requirements={"version"="\d+"},
     *     defaults={"_routeScope"={"api"}}
     * )
     * @return JsonResponse
     */
    public function schedulePagesSynchronizationAction(Context $context)
    {
        $errorCode = '';
        $isScheduled = false;
        $responseCode = 200;
        try {
            $this->stylaPagesSynchronizer->schedulePagesSynchronization($context);
            $isScheduled = true;
        } catch (SynchronizationIsAlreadyRunning $exception) {
            $errorCode = 'SYNCHRONIZATION_IS_ALREADY_RUNNING';
        } catch (UseCaseInteractorException $exception) {
            $errorCode = $exception->getErrorCode();
            $responseCode = 501;
        } catch (\Throwable $exception) {
            $errorCode = 'SYNCHRONIZATION_IS_FAILED';
            $responseCode = 503;

            $this->logger->error('Pages Synchronization schedule failed', ['exception' => $exception]);
        }

        return new JsonResponse(['isScheduled' => $isScheduled, 'errorCode' => $errorCode], $responseCode);
    }

    /**
     * @Route(
     *     "/_action/refresh-details/{pageId}",
     *     name="api.styla.page.refresh-details",
     *     methods={"POST"},
     *     requirements={"version"="\d+"}
     * )
     * @return JsonResponse
     */
    public function refreshPageDetailsAction(string $pageId, Context $context)
    {
        $errorCode = '';
        $isSuccess = false;
        $responseCode = 200;
        try {
            $searchResult = $this->repository->search(
                new Criteria([$pageId]),
                $context
            );
            if ($searchResult->getTotal() === 0) {
                throw new \RuntimeException('Page entity was not found');
            }

            $this->stylaPagesInteractor->refreshPageDetails($searchResult->first());
            $isSuccess = true;
        } catch (PageNotFoundException $exception) {
            $errorCode = 'PAGE_NOT_FOUND';
        } catch (UseCaseInteractorException $exception) {
            $errorCode = $exception->getErrorCode();
            $responseCode = 501;
        } catch (\Throwable $exception) {
            $errorCode = 'PAGE_REFRESH_FAILED';
            $responseCode = 503;
        }

        return new JsonResponse(['isSuccess' => $isSuccess, 'errorCode' => $errorCode], $responseCode);
    }
}
