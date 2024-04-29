<?php

namespace Styla\CmsIntegration\Test\DataFixtures;

use Psr\Container\ContainerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class LoadCategoryData extends AbstractTestDataFixture
{
    private $categoriesData = [
        [
            'name' => 'Bar Category',
            'uri' => 'page/bar-page',
            'cmsPageId' => 'styla_cms_integration.page.default_listing',
            'active' => true,
            'parentId' => 'styla_cms_integration.category.root',
            'level' => 2,
            'type' => 'page',
        ],
        [
            'name' => 'Foo Category',
            'uri' => 'page/foo-page',
            'cmsPageId' => 'styla_cms_integration.page.foo',
            'active' => true,
            'parentId' => 'styla_cms_integration.category.root',
            'level' => 2,
            'type' => 'page',
        ],
        [
            'name' => 'Quuz Category',
            'uri' => 'page/quuz',
            'cmsPageId' => 'styla_cms_integration.page.quuz',
            'active' => true,
            'parentId' => 'styla_cms_integration.category.root',
            'level' => 2,
            'type' => 'page',
        ],
        [
            'name' => 'Corge Category',
            'uri' => 'page/corge',
            'cmsPageId' => 'styla_cms_integration.page.corge',
            'active' => true,
            'parentId' => 'styla_cms_integration.category.root',
            'level' => 2,
            'type' => 'page',
        ],
    ];

    public function execute(ContainerInterface $container, ReferencesRegistry $referencesRegistry)
    {
        /** @var EntityRepository $categoryRepository */
        $categoryRepository = $container->get('category.repository');
        $this->registerRootCategoryReference($categoryRepository, $referencesRegistry);

        $storefrontSalesChannel = $referencesRegistry
            ->getByReference('styla_cms_integration.sales_channel.storefront');

        $urlToCategoryMap = [];
        foreach ($this->categoriesData as $record) {
            $uri = $record['uri'];
            unset($record['uri']);

            $this->resolveReferencesAsIdsIfExists($referencesRegistry, $record, ['cmsPageId', 'parentId']);
            $categoryEntity = $this->createEntity($categoryRepository, $record);
            $urlToCategoryMap[$uri] = $categoryEntity;
        }

        // Workaround: Divided from general loop because of Shopware will update url and make it not canonical
        // during the new category creation
        foreach ($urlToCategoryMap as $uri => $categoryEntity) {
            $this->updateSeoUrl($container, $categoryEntity->getUniqueIdentifier(), $storefrontSalesChannel, $uri);
        }
    }

    private function registerRootCategoryReference(EntityRepository $categoryRepository, ReferencesRegistry $referencesRegistry)
    {
        $criteria = new Criteria();

        $criteria->addFilter(new EqualsFilter('parentId', null));

        $category = $categoryRepository->search($criteria, Context::createDefaultContext())->getEntities()->first();
        $referencesRegistry->setReference('styla_cms_integration.category.root', $category);
    }

    public function getDependenciesList(): array
    {
        return [new LoadCmsPageWithBlocks(), new RegisterDefaultSalesChannel()];
    }
}
