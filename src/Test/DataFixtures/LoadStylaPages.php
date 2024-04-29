<?php

namespace Styla\CmsIntegration\Test\DataFixtures;

use Psr\Container\ContainerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;

class LoadStylaPages extends AbstractTestDataFixture
{
    private array $pagesData = [
        [
            'name' => '',
            'accountName' => 'foo_account',
            'domain' => 'http://example.com',
            'path' => '',
            'title' => 'Home Page',
            'seoTitle' => 'Home Page',
            'position' => null,
            'stylaUpdatedAt' => '2015-01-01 00:00:00',
        ],
        [
            'name' => 'Foo page',
            'accountName' => 'foo_account',
            'domain' => 'http://example.com',
            'path' => '/page/foo-page',
            'title' => 'Foo page',
            'seoTitle' => 'Foo page seo title',
            'position' => null,
            'stylaUpdatedAt' => '2018-01-01 00:00:00',
        ],
        [
            'name' => 'Bar page',
            'accountName' => 'foo_account',
            'domain' => 'http://example.com',
            'path' => '/page/bar-page',
            'title' => 'Bar page',
            'seoTitle' => 'Bar page seo title',
            'position' => null,
            'stylaUpdatedAt' => '2019-01-01 00:00:00',
        ],
        [
            'name' => 'Qux page',
            'accountName' => 'foo_account',
            'domain' => 'http://example.com',
            'path' => '/page/qux-page',
            'title' => 'Qux page',
            'seoTitle' => 'Qux page seo title',
            'position' => null,
            'stylaUpdatedAt' => '2019-02-02 00:00:00',
        ],
        [
            'name' => 'Styla Shopping Cart page',
            'accountName' => 'foo_account',
            'domain' => 'http://example.com',
            'path' => '/checkout/cart',
            'title' => 'Styla Shopping Cart page',
            'seoTitle' => 'Styla Shopping Cart page title',
            'position' => null,
            'stylaUpdatedAt' => '2019-02-02 00:00:00',
        ],
        [
            'name' => 'Qux page',
            'accountName' => 'bar_account',
            'domain' => 'http://example.com',
            'path' => '/page/qux-page',
            'title' => 'Qux page',
            'seoTitle' => 'Qux page seo title',
            'position' => null,
            'stylaUpdatedAt' => '2019-02-02 00:00:00',
        ]
    ];

    public function execute(ContainerInterface $container, ReferencesRegistry $referencesRegistry)
    {
        /** @var EntityRepository $repository */
        $repository = $container->get('styla_cms_page.repository');

        $preparedPagesData = $this->pagesData;
        foreach ($preparedPagesData as &$item) {
            $item['stylaUpdatedAt'] = date_create_from_format(
                'Y-m-d H:i:s',
                $item['stylaUpdatedAt'],
                new \DateTimeZone('UTC')
            );
        }

        $repository->create($preparedPagesData, Context::createDefaultContext());
    }
}
