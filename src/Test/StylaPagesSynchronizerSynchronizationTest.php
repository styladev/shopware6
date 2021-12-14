<?php

namespace Styla\CmsIntegration\Test;

use Psr\Http\Message\RequestInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Test\TestCaseBase\KernelLifecycleManager;
use Styla\CmsIntegration\Configuration\ConfigurationFactory;
use Styla\CmsIntegration\Entity\StylaPage\StylaPageCollection;
use Styla\CmsIntegration\Test\Constraint\StylaPagesMatchConstraint;
use Styla\CmsIntegration\Test\Constraint\StylaSynchronizationMatchConstraint;
use Styla\CmsIntegration\Test\DataFixtures\TestDataFixturesInterface;

class StylaPagesSynchronizerSynchronizationTest extends AbstractStylaPagesSynchronizationTestCase
{
    /**
     * @after
     */
    public function stopTransactionAfter(): void
    {
        parent::stopTransactionAfter();

        KernelLifecycleManager::ensureKernelShutdown();
    }

    /**
     * @dataProvider synchronizeStylaPagesDataProvider
     */
    public function testSynchronizeStylaPagesSingleBatch(string $useCasePath)
    {
        $useCaseParams = include $useCasePath;

        /**
         * @var TestDataFixturesInterface[] $dataFixtures
         * @var StylaPagesMatchConstraint $stylaPagesConstraint
         * @var StylaSynchronizationMatchConstraint $stylaSynchronizationConstraint
         * @var string $expectedPagesRequestResponse
         */
        list (
            $dataFixtures,
            $stylaPagesConstraint,
            $stylaSynchronizationConstraint,
            $expectedPagesRequestResponse
        ) = $useCaseParams;

        $this->executeFixtures($dataFixtures);

        $this->guzzleClient
            ->expects($this->any())
            ->method('send')
            ->willReturn($expectedPagesRequestResponse);

        $context = Context::createDefaultContext();
        $this->stylaPagesSynchronizer->schedulePagesSynchronization($context);

        $synchronization = $this->getSingleSynchronization($context);
        $this->stylaPagesSynchronizer->synchronizeStylaPages($synchronization->getId(), $context);

        $synchronization = $this->getSingleSynchronization($context);
        self::assertThat($synchronization, $stylaSynchronizationConstraint);

        $stylaPages = $this->getPagesOrderedByPath($context)->getElements();
        self::assertThat($stylaPages, $stylaPagesConstraint);
    }

    protected function getPagesOrderedByPath(Context $context): StylaPageCollection
    {
        $criteria = new Criteria();
        $criteria->addSorting(new FieldSorting('accountName'));
        $criteria->addSorting(new FieldSorting('path'));
        $criteria->addSorting(new FieldSorting('name'));

        /** @var StylaPageCollection $collection */
        $collection = $this->stylaPagesRepository->search($criteria, $context)->getEntities();

        return $collection;
    }

    public function synchronizeStylaPagesDataProvider()
    {
        return [
            'Success Synchronization of the single batch' => [
                'useCasePath' => __DIR__.
                    '/TestCases/StylaPagesSynchronization/use_case_1_success_synchronization_single_batch.php'
            ],
            'Failed Synchronization when response is not json' => [
                'useCasePath' => __DIR__.
                    '/TestCases/StylaPagesSynchronization/' .
                    'use_case_2_failed_synchronization_when_response_is_not_json.php'
            ],
            'Failed Synchronization when response is incorrect json' => [
                'useCasePath' => __DIR__.
                    '/TestCases/StylaPagesSynchronization/' .
                    'use_case_3_failed_synchronization_when_response_is_incorrect_json.php'
            ],
            'Failed Synchronization when response is not an array' => [
                'useCasePath' => __DIR__.
                    '/TestCases/StylaPagesSynchronization/' .
                    'use_case_3.1_failed_synchronization_when_response_is_not_an_array.php'
            ],
            'Failed Synchronization when response code is not 200-299' => [
                'useCasePath' => __DIR__.
                    '/TestCases/StylaPagesSynchronization/' .
                    'use_case_3.2_failed_synchronization_when_response_is_not_200.php'
            ],
            'Success Synchronization when not all expected fields are provided in json' => [
                'useCasePath' => __DIR__.
                    '/TestCases/StylaPagesSynchronization/' .
                    'use_case_4_success_synchronization_when_minimum_fields_are_provided_in_json.php'
            ],
            'Success Synchronization when timestamp is invalid' => [
                'useCasePath' => __DIR__.
                    '/TestCases/StylaPagesSynchronization/' .
                    'use_case_5_success_synchronization_when_timestamp_is_invalid.php'
            ],
            'Success Synchronization when update of the existing pages' => [
                'useCasePath' => __DIR__.
                    '/TestCases/StylaPagesSynchronization/' .
                    'use_case_6_update_of_existing_pages_during_synchronization.php'
            ]
        ];
    }

    /**
     * @dataProvider synchronizeStylaPagesForMultipleAccountsDataProvider
     */
    public function testSynchronizeStylaPagesMultipleAccount(string $useCasePath)
    {
        $this->systemConfigService
            ->set(
                ConfigurationFactory::PREFIX . 'accountNames',
                ['default' => 'foo_account', 'de_DE' => 'bar_account']
            );

        $useCaseParams = include $useCasePath;

        /**
         * @var TestDataFixturesInterface[] $dataFixtures
         * @var StylaPagesMatchConstraint $stylaPagesConstraint
         * @var StylaSynchronizationMatchConstraint $stylaSynchronizationConstraint
         * @var string $expectedPagesRequestResponse
         */
        list (
            $dataFixtures,
            $stylaPagesConstraint,
            $stylaSynchronizationConstraint,
            $expectedFooAccountPagesRequestResponse,
            $expectedBarAccountPagesRequestResponse,
            ) = $useCaseParams;

        $this->executeFixtures($dataFixtures);

        $this->guzzleClient
            ->expects($this->exactly(2))
            ->method('send')
            ->willReturnCallback(
                function(RequestInterface $request)
                    use ($expectedFooAccountPagesRequestResponse, $expectedBarAccountPagesRequestResponse) {
                    if (strpos($request->getUri(), 'delta/foo_account?') !== false) {
                        return $expectedFooAccountPagesRequestResponse;
                    }

                    return $expectedBarAccountPagesRequestResponse;
                }
            );

        $context = Context::createDefaultContext();
        $this->stylaPagesSynchronizer->schedulePagesSynchronization($context);

        $synchronization = $this->getSingleSynchronization($context);
        $this->stylaPagesSynchronizer->synchronizeStylaPages($synchronization->getId(), $context);

        $synchronization = $this->getSingleSynchronization($context);
        self::assertThat($synchronization, $stylaSynchronizationConstraint);

        $stylaPages = $this->getPagesOrderedByPath($context)->getElements();
        self::assertThat($stylaPages, $stylaPagesConstraint);
    }

    public function synchronizeStylaPagesForMultipleAccountsDataProvider()
    {
        return [
            'Success Synchronization when update of the existing pages for two accounts' => [
                'useCasePath' => __DIR__.
                    '/TestCases/StylaPagesSynchronization/' .
                    'use_case_6.1_update_of_existing_pages_works_for_multiple_accounts_during_synchronization.php'
            ],
            'Synchronization will be finished for account even if another account sync failed ' .
            '(Synchronization entity status still expected to be "Failed")' => [
                'useCasePath' => __DIR__.
                    '/TestCases/StylaPagesSynchronization/' .
                    'use_case_7_sync_will_be_finished_for_account_even_if_another_account_sync_failed.php'
            ],
        ];
    }
}
