<?php

namespace Styla\CmsIntegration\Twig\Extension;

use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Storefront\Page\LandingPage\LandingPage;
use Shopware\Storefront\Page\Maintenance\MaintenancePage;
use Shopware\Storefront\Page\Navigation\Error\ErrorPage;
use Shopware\Storefront\Page\Navigation\NavigationPage;
use Shopware\Storefront\Page\Page;
use Shopware\Storefront\Page\Product\ProductPage;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class StylaModuleContentTwigExtension extends AbstractExtension
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('has_styla_elements', [$this, 'hasStylaElements']),
        ];
    }

    public function hasStylaElements($page)
    {
        if (!$page instanceof Page) {
            return false;
        }
        try {
            $cmsPage = $this->getCmsPage($page);
            if (!$cmsPage) {
                return false;
            }

            $result = $cmsPage->getElementsOfType('styla-module-content');

            return count($result) > 0;
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Exception happened wile checking for the existence of the styla modules in the page',
                [
                    'exception' => $exception
                ]
            );
        }

        return false;
    }


    private function getCmsPage(Page $page): ?CmsPageEntity
    {
        $cmsPage = null;

        /**
         * Instance was checked instead of the check for method existence in order to be sure about
         * the method signature and to be clear about this method's dependencies that should help in maintenance
         * There is no common interface for "getCmsPage" method for now, so it was decided to check multiple classes
         */
        if ($page instanceof NavigationPage
            || $page instanceof LandingPage
            || $page instanceof ProductPage
            || $page instanceof ErrorPage
            || $page instanceof MaintenancePage
        ) {
            $cmsPage = $page->getCmsPage();
        }

        return $cmsPage;
    }
}
