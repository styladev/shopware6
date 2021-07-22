<?php

namespace Styla\CmsIntegration\Test;

use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Framework\Context;
use Styla\CmsIntegration\Test\DataFixtures\LoadCategoryData;
use Styla\CmsIntegration\Test\DataFixtures\LoadLandingPageData;
use Styla\CmsIntegration\Test\DataFixtures\LoadMaintenancePageConfigurationData;
use Styla\CmsIntegration\Test\DataFixtures\LoadNotFoundPageConfigurationData;
use Styla\CmsIntegration\Test\DataFixtures\LoadProductData;
use Styla\CmsIntegration\Test\DataFixtures\RegisterDefaultSalesChannel;
use Symfony\Component\HttpFoundation\RedirectResponse;

class StylaModuleContentRenderingTest extends AbstractStorefrontPageRenderingTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->executeFixtures(
            [
                new LoadCategoryData(),
                new LoadProductData(),
                new LoadLandingPageData(),
                new LoadMaintenancePageConfigurationData(),
                new LoadNotFoundPageConfigurationData(),
                new RegisterDefaultSalesChannel()
            ]
        );
    }

    /**
     * @dataProvider moduleContentIsRenderedDataProvider
     */
    public function testModuleContentIsRendered(string $url, int $expectedStatusCode, string $expectedSlotReference)
    {
        $request = $this->createValidTestRequest($url);

        /** @var CmsSlotEntity $expectedSlot */
        $expectedSlot = $this->getByReference($expectedSlotReference);

        $slotConfig = $expectedSlot->getConfig();
        $expectedSlotId = $slotConfig['slotId']['value'];

        $actualResponse = $this->getHttpKernel()->handle($request)->getResponse();

        self::assertEquals($expectedStatusCode, $actualResponse->getStatusCode());

        self::assertStringContainsString(
            '<script type="text/javascript" src="https://engine.styla.com/init.js" async></script>',
            $actualResponse->getContent()
        );

        self::assertStringContainsString(
            "<div data-styla-slot=\"$expectedSlotId\"></div>",
            $actualResponse->getContent()
        );
    }

    public function moduleContentIsRenderedDataProvider()
    {
        return [
            'On the category page' => [
                'uri' => '/page/foo-page',
                'expectedStatusCode' => 200,
                'expectedSlot' => 'styla_cms_integration.page.foo.block.foo.slot.foo'
            ],
            'On the product details page' => [
                'uri' => '/product/foo1',
                'expectedStatusCode' => 200,
                'expectedSlot' => 'styla_cms_integration.page.bar.block.foo.slot.foo'
            ],
            'On the landing pages' => [
                'uri' => '/landing-page-foo',
                'expectedStatusCode' => 200,
                'expectedSlot' => 'styla_cms_integration.landing_page.foo.block.foo.slot.foo'
            ],
            'On the 404 pages' => [
                'uri' => '/some-not-existing-page',
                'expectedStatusCode' => 404,
                'expectedSlot' => 'styla_cms_integration.page.quux.block.foo.slot.foo'
            ],
            'On the category page in case if element was passed to the another block' => [
                'uri' => '/page/corge',
                'expectedStatusCode' => 200,
                'expectedSlot' => 'styla_cms_integration.page.corge.block.foo.slot.foo'
            ],
        ];
    }

    public function testStylaEngineScriptIsNotLoadedIfPageHasNoStylaModule()
    {
        $request = $this->createValidTestRequest('/page/quuz');

        $actualResponse = $this->getHttpKernel()->handle($request)->getResponse();

        self::assertEquals(200, $actualResponse->getStatusCode());

        self::assertStringNotContainsString(
            '<script type="text/javascript" src="https://engine.styla.com/init.js" async></script>',
            $actualResponse->getContent()
        );

        self::assertStringContainsString('Quuz slot content', $actualResponse->getContent());
    }

    public function testModuleContentIsRenderedOnMaintenancePage()
    {
        $request = $this->createValidTestRequest('/page/quuz');

        $storefrontSalesChannel = $this->getByReference('styla_cms_integration.sales_channel.storefront');
        $this->getContainer()->get('sales_channel.repository')
            ->update(
                [['id' => $storefrontSalesChannel->getId(), 'maintenance' => true]],
                Context::createDefaultContext()
            );

        /** @var RedirectResponse $actualResponse */
        $actualResponse = $this->getHttpKernel()->handle($request)->getResponse();
        self::assertEquals(307, $actualResponse->getStatusCode());

        $request = $this->createValidTestRequest($actualResponse->getTargetUrl());
        $actualResponse = $this->getHttpKernel()->handle($request)->getResponse();
        self::assertEquals(503, $actualResponse->getStatusCode());

        self::assertStringContainsString(
            '<script type="text/javascript" src="https://engine.styla.com/init.js" async></script>',
            $actualResponse->getContent()
        );

        self::assertStringContainsString(
            '<div data-styla-slot="3cbce417-436d-4b2f-a70e-ba93c71782d0"></div>',
            $actualResponse->getContent()
        );
    }
}
