<?php

namespace Styla\CmsIntegration\Test;

use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Styla\CmsIntegration\Configuration\ConfigurationFactory;
use Styla\CmsIntegration\Test\DataFixtures\LoadCategoryData;
use Styla\CmsIntegration\Test\DataFixtures\LoadStylaPages;
use Styla\CmsIntegration\Test\DataFixtures\TestDataFixturesInterface;
use Symfony\Component\DomCrawler\Crawler;

class ShopwarePagesRenderingTest extends AbstractStorefrontPageRenderingTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setStylaSystemSettings();

        $this->executeFixtures([new LoadStylaPages(), new LoadCategoryData()]);
    }

    private function setStylaSystemSettings()
    {
        $germanLanguage = $this->getLanguageByName('Deutsch');
        $this->systemConfigService
            ->set(
                ConfigurationFactory::PREFIX . 'accountNames',
                ['default' => 'foo_account', $germanLanguage->getId() => 'bar_account']
            );
    }

    /**
     * @dataProvider shopwarePageWasOverriddenDataProvider
     */
    public function testShopwarePageWasOverridden(string $useCasePath)
    {
        $useCaseParams = include $useCasePath;

        /**
         * @var TestDataFixturesInterface[] $dataFixtures
         * @var GuzzleRequest $expectedGuzzleRequest
         * @var GuzzleResponse $guzzleResponseStub
         * @var array $expectedPageParts
         */
        list (
            $expectedGuzzleRequest,
            $guzzleResponseStub,
            $expectedPageUri,
            $expectedPageParts
        ) = $useCaseParams;

        // Allow to override shopping cart page
        $this->systemConfigService
            ->set(ConfigurationFactory::PREFIX . 'extraPagesAllowedToOverride', '/checkout/cart');

        $this->guzzleClient->expects($this->once())
            ->method('send')
            ->with($expectedGuzzleRequest)
            ->willReturn($guzzleResponseStub);

        $request = $this->createValidTestRequest($expectedPageUri);

        $actualResponse = $this->getHttpKernel()->handle($request)->getResponse();

        self::assertEquals(200, $actualResponse->getStatusCode());

        foreach ($expectedPageParts as $expectedPagePart) {
            self::assertStringContainsString(
                $expectedPagePart,
                $actualResponse->getContent()
            );
        }
    }

    public function shopwarePageWasOverriddenDataProvider(): array
    {
        return [
            'It is possible to rewrite page that does not exist in shopware' => [
                'useCasePath' => __DIR__.
                    '/TestCases/StylaPagesRendering/use_case_1_styla_page_rendered_if_shopware_page_not_found.php'
            ],
            'It is possible to rewrite shopware home page' => [
                'useCasePath' => __DIR__.
                    '/TestCases/StylaPagesRendering/use_case_2_shopware_home_page_was_replaced_by_styla_page.php'
            ],
            'It is possible to rewrite shopware category page with styla page when path identical' => [
                'useCasePath' => __DIR__.
                    '/TestCases/StylaPagesRendering/use_case_3_shopware_category_page_was_replaced_by_styla_page.php'
            ],
            'It is possible to rewrite shopware page with styla page when path is defined in the configuration' => [
                'useCasePath' => __DIR__. '/TestCases/StylaPagesRendering/' .
                    'use_case_4_shopware_pages_listed_in_styla_config_can_be_overridden.php'
            ],
        ];
    }


    /**
     * @dataProvider shopwarePageWasNotOverriddenDataProvider
     */
    public function testShopwarePageWasNotOverridden(
        int $expectedStatus,
        string $pageUri,
        ?string $expectedTitle,
        string $language
    ) {
        $this->guzzleClient->expects($this->never())
            ->method('send');

        $language = $this->getLanguageByName($language);


        /** @var EntityRepositoryInterface $salesChannelRepository */
        $salesChannelRepository = $this->getContainer()->get('sales_channel.repository');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('typeId', Defaults::SALES_CHANNEL_TYPE_STOREFRONT));
        /** @var SalesChannelEntity $salesChannel */
        $salesChannel = $salesChannelRepository->search($criteria, Context::createDefaultContext())
            ->getEntities()->first();
        $salesChannelRepository->update(
            [['id' => $salesChannel->getId(), 'languageId' => $language->getId()]],
            Context::createDefaultContext()
        );

        /** @var EntityRepositoryInterface $domainsRepository */
        $domainsRepository = $this->getContainer()->get('sales_channel_domain.repository');
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannel->getId()));
        $domain = $domainsRepository->search($criteria, Context::createDefaultContext())->first();

        $domainsRepository->update(
            [['id' => $domain->getId(), 'languageId' => $language->getId()]],
            Context::createDefaultContext()
        );

        $request = $this->createValidTestRequest($pageUri, $language);

        $actualResponse = $this->getHttpKernel()->handle($request)->getResponse();

        self::assertEquals($expectedStatus, $actualResponse->getStatusCode());

        if ($expectedTitle) {
            $crawler = new Crawler($actualResponse->getContent());

            $titleNode = $crawler->filterXPath('//title')->first();
            $title = $titleNode->text();

            self::assertStringContainsString($expectedTitle, $title);
        }

        self::assertStringNotContainsString(
            '<script type="text/javascript" src="https://engine.styla.com/init.js" async></script>',
            $actualResponse->getContent()
        );
    }

    private function getLanguageByName(string $name): LanguageEntity
    {
        /** @var EntityRepositoryInterface $languageRepository */
        $languageRepository = $this->getContainer()->get('language.repository');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $name));

        /**
         * @var LanguageEntity $language
         */
        $language = $languageRepository->search($criteria, Context::createDefaultContext())->getEntities()->first();

        return $language;
    }

    public function shopwarePageWasNotOverriddenDataProvider(): array
    {
        return [
            'It is not possible to override pages with styla pages if it is not in the white list and not configured in' .
            ' the plugin configuration' => [
                'expectedStatus' => 200,
                'pageUri' => '/checkout/cart',
                'expectedTitle' => 'Shopping cart',
                'language' => 'English'
            ],
            'Shopware not found page should be rendered if matched styla page is not found' => [
                'expectedStatus' => 404,
                'pageUri' => '/page/some-not-existed-page',
                'expectedTitle' => null,
                'language' => 'English'
            ],
            'Shopware not found page should be rendered if matched styla page is not found for this language' => [
                'expectedStatus' => 404,
                'pageUri' => '/page/foo-page',
                'expectedTitle' => null,
                'language' => 'Deutsch'
            ],
        ];
    }
}
