<?php

namespace Styla\CmsIntegration\Controller\Storefront;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Page\GenericPageLoaderInterface;
use Styla\CmsIntegration\Entity\StylaPage\StylaPage;
use Styla\CmsIntegration\UseCase\StylaPagesInteractor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class StylaPageController extends StorefrontController
{
    private StylaPagesInteractor $stylaPagesInteractor;
    private GenericPageLoaderInterface $genericLoader;

    public function __construct(StylaPagesInteractor $stylaPagesInteractor, GenericPageLoaderInterface $genericLoader)
    {
        $this->stylaPagesInteractor = $stylaPagesInteractor;
        $this->genericLoader = $genericLoader;
    }

    /**
     * @Route(
     *     "/styla/page/render",
     *     name="styla.page.storefront.render",
     *     methods={"GET"}
     * )
     */
    public function renderStylaPage(StylaPage|null $stylaPage, Request $request, SalesChannelContext $context)
    {
        if (!$stylaPage) {
            return $this->renderStorefront('@StylaCmsIntegrationPlugin/storefront/styla_page/not_found.html.twig', []);
        }

        $page = $this->genericLoader->load($request, $context);

        $pageDetails = $this->stylaPagesInteractor->getPageDetails($stylaPage);

        return $this->renderStorefront(
            '@StylaCmsIntegrationPlugin/storefront/styla_page/page.html.twig',
            ['stylaPage' => $stylaPage, 'stylaPageDetails' => $pageDetails, 'page' => $page]
        );
    }
}
