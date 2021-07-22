<?php

namespace Styla\CmsIntegration\Test\DataFixtures;

use Psr\Container\ContainerInterface;
use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class LoadCmsPageData extends AbstractTestDataFixture
{
    private array $data = [
        'styla_cms_integration.page.foo' => [
            'name' => 'Foo page',
            'type' => 'page',
        ],
        'styla_cms_integration.page.bar' => [
            'name' => 'Bar CMS page',
            'type' => 'page',
        ],
        'styla_cms_integration.page.baz' => [
            'name' => 'Baz CMS page',
            'type' => 'landingpage',
        ],
        'styla_cms_integration.page.qux' => [
            'name' => 'Maintenance page',
            'type' => 'page',
        ],
        'styla_cms_integration.page.quux' => [
            'name' => 'Not found page',
            'type' => 'page',
        ],
        'styla_cms_integration.page.quuz' => [
            'name' => 'Page without syla module',
            'type' => 'page',
        ],
        'styla_cms_integration.page.corge' => [
            'name' => 'Page with styla element in the regular text block',
            'type' => 'page',
        ],
    ];

    public function execute(ContainerInterface $container, ReferencesRegistry $referencesRegistry)
    {
        /** @var EntityRepositoryInterface $cmsPageRepository */
        $cmsPageRepository = $container->get('cms_page.repository');

        foreach ($this->data as $reference => $record) {
            $entity = $this->createEntity($cmsPageRepository, $record);
            $referencesRegistry->setReference($reference, $entity);
        }

        $page = $this->getDefaultCmsListingPage($cmsPageRepository);
        $referencesRegistry->setReference('styla_cms_integration.page.default_listing', $page);
    }

    private function getDefaultCmsListingPage(EntityRepositoryInterface $cmsPageRepository): CmsPageEntity
    {
        $criteria = new Criteria();

        $criteria->addFilter(new EqualsFilter('name', 'Default category layout'));

        return $cmsPageRepository->search($criteria, Context::createDefaultContext())->getEntities()->first();
    }
}
