<?php

namespace Styla\CmsIntegration\Test\DataFixtures;

use Psr\Container\ContainerInterface;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class LoadProductData extends AbstractTestDataFixture
{
    protected array $data = [
        'styla_cms_integration.product.foo' => [
            'active' => true,
            'name' => 'foo1',
            'productNumber' => 'foo1',
            'stock' => 111,
            'price' => [
                [
                    'net' => 1,
                    'gross' => 1,
                    'currencyId' => Defaults::CURRENCY,
                    'linked' => false
                ]
            ],
            'seoUrl' => 'product/foo1',
            'taxId' => 'styla_cms_integration.tax.standard',
            'cmsPageId' => 'styla_cms_integration.page.bar'
        ]
    ];

    public function execute(ContainerInterface $container, ReferencesRegistry $referencesRegistry)
    {
        $productRepository = $container->get('product.repository');

        $storefrontSalesChannel = $referencesRegistry->getByReference(
            'styla_cms_integration.sales_channel.storefront'
        );

        $urlToProductHashMap = [];
        foreach ($this->data as $reference => $record) {
            $seoUrl = $record['seoUrl'];
            unset($record['seoUrl']);

            $this->resolveReferencesAsIdsIfExists($referencesRegistry, $record, ['taxId', 'cmsPageId']);

            /** @var ProductEntity $productEntity */
            $productEntity = $this->createEntity($productRepository, $record);

            $referencesRegistry->setReference($reference, $productEntity);

            $this->createVisibilities($container, $productEntity, $storefrontSalesChannel);

            $urlToProductHashMap[$seoUrl] = $productEntity;
        }

        // Workaround: Divided from general loop because of Shopware will update url and make it not canonical
        // during the new visibility creation
        foreach ($urlToProductHashMap as $seoUrl => $productEntity) {
            $this->updateSeoUrl($container, $productEntity->getId(), $storefrontSalesChannel, $seoUrl);
        }
    }

    private function createVisibilities(
        ContainerInterface $container,
        ProductEntity $product,
        SalesChannelEntity $salesChannel
    ) {
        /** @var EntityRepository $repository */
        $repository = $container->get('product_visibility.repository');

        $productVisibilityData = [
            'productId' => $product->getId(),
            'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL,
            'salesChannelId' => $salesChannel->getId(),
        ];

        $repository->create([$productVisibilityData], Context::createDefaultContext());
    }

    public function getDependenciesList(): array
    {
        return [
            new RegisterTaxesReferences(),
            new RegisterDefaultSalesChannel(),
            new LoadCmsPageData()
        ];
    }
}
