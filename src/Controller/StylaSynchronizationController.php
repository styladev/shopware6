<?php

namespace Styla\CmsIntegration\Controller;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Styla\CmsIntegration\Entity\StylaIntegration\StylaPagesSynchronization;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * use RouteScope(scopes={"api"})
 * @Route(
 *     "api/styla/synchronization"
 * )
 */
class StylaSynchronizationController
{
    private EntityRepository $synchronizationRepository;
    private LoggerInterface $logger;

    public function __construct(EntityRepository $synchronizationRepository, LoggerInterface $logger)
    {
        $this->synchronizationRepository = $synchronizationRepository;
        $this->logger = $logger;
    }

    /**
     * @Route(
     *     "/page/_action/get_last_success_date_time",
     *     name="api.styla.synchronization.page.get-last-success-date-time",
     *     methods={"GET"},
     *     requirements={"version"="\d+"}
     * )
     * @return JsonResponse
     */
    public function getLastsSuccessPageSynchronizationDateAction(Context $context)
    {
        try {
            $criteria = new Criteria();

            $criteria->addSorting(new FieldSorting('finishedAt', FieldSorting::DESCENDING));
            $criteria->addFilter(new EqualsFilter('status', StylaPagesSynchronization::STATUS_SUCCESS));
            $criteria->setLimit(1);

            $synchronizationResult = $this->synchronizationRepository->search($criteria, $context);

            /** @var StylaPagesSynchronization $synchronization */
            $synchronization = $synchronizationResult->first();

            return new JsonResponse(
                [
                    'result' => $synchronization ? $synchronization->getFinishedAt()
                        ->format(\DateTimeInterface::ATOM) : null
                ]
            );
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Exception happened during fetch of the last success page synchronization date',
                [
                    'exception' => $exception
                ]
            );

            throw new HttpException(500, 'Internal server error');
        }
    }
}
