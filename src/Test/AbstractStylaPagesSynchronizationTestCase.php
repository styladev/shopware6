<?php

namespace Styla\CmsIntegration\Test;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Styla\CmsIntegration\Entity\StylaIntegration\StylaPagesSynchronization;
use Styla\CmsIntegration\Entity\StylaIntegration\StylaPagesSynchronizationsCollection;
use Styla\CmsIntegration\Entity\StylaIntegration\StylaSynchronizationDalHelper;
use Styla\CmsIntegration\UseCase\StylaPagesSynchronizer;

abstract class AbstractStylaPagesSynchronizationTestCase extends AbstractTestCase
{
    protected StylaSynchronizationDalHelper $synchronizationDalHelper;
    protected StylaPagesSynchronizer $stylaPagesSynchronizer;
    protected EntityRepository $stylaSynchronizationRepository;

    protected function setUp(): void
    {
        $container = $this->getContainer();
        /** @var StylaSynchronizationDalHelper $helper */
        $helper = $container->get(StylaSynchronizationDalHelper::class);
        $this->synchronizationDalHelper = $helper;

        /** @var StylaPagesSynchronizer $synchronizer */
        $synchronizer = $container->get(StylaPagesSynchronizer::class);
        $this->stylaPagesSynchronizer = $synchronizer;

        /** @var EntityRepository $repository */
        $repository = $this->getContainer()->get('styla_cms_pages_synchronization.repository');
        $this->stylaSynchronizationRepository = $repository;

        /** @var Connection $connection */
        $connection = $container->get(Connection::class);
        $this->connection = $connection;

        parent::setUp();
    }

    protected function getSingleSynchronization(Context $context): StylaPagesSynchronization
    {
        $synchronizations = $this->getOrderedSynchronizationsList($context);
        self::assertCount(1, $synchronizations);

        /** @var StylaPagesSynchronization $synchronization */
        $synchronization = $synchronizations->first();

        return $synchronization;
    }

    protected function getOrderedSynchronizationsList(Context $context): StylaPagesSynchronizationsCollection
    {
        $criteria = new Criteria();
        $criteria->addSorting(new FieldSorting('createdAt', FieldSorting::ASCENDING));
        /** @var StylaPagesSynchronizationsCollection $synchronizations */
        $synchronizations = $this->stylaSynchronizationRepository
            ->search($criteria, $context)->getEntities();

        return $synchronizations;
    }
}
