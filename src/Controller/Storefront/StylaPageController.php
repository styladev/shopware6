<?php

namespace Styla\CmsIntegration\Controller\Storefront;

use Shopware\Core\Framework\Adapter\Cache\Event\AddCacheTagEvent;
use Shopware\Core\Framework\Adapter\Cache\Http\CacheStore;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Page\GenericPageLoaderInterface;
use Styla\CmsIntegration\Entity\StylaPage\StylaPage;
use Styla\CmsIntegration\Styla\Synchronization\CacheInvalidator;
use Styla\CmsIntegration\UseCase\StylaPagesInteractor;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ["_routeScope" => ["storefront"]])]
class StylaPageController extends StorefrontController
{
    private StylaPagesInteractor $stylaPagesInteractor;
    private GenericPageLoaderInterface $genericLoader;

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        StylaPagesInteractor $stylaPagesInteractor,
        GenericPageLoaderInterface $genericLoader,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->stylaPagesInteractor = $stylaPagesInteractor;
        $this->genericLoader = $genericLoader;
        $this->eventDispatcher = $eventDispatcher;
    }

    #[Route(
        path: "/styla/page/render",
        name: "styla.page.storefront.render",
        methods: ["GET"]
    )]
    public function renderStylaPage(StylaPage|null $stylaPage, Request $request, SalesChannelContext $context)
    {
        if (!$stylaPage) {
            return $this->renderStorefront('@Storefront/storefront/page/error/error-404.html.twig', []);
        }

        // Tag the http cache entry so a synchronization can invalidate exactly this page
        $cacheTag = CacheInvalidator::httpCacheTag($stylaPage->getId());
        $this->eventDispatcher->dispatch(new AddCacheTagEvent($cacheTag));

        $page = $this->genericLoader->load($request, $context);

        $pageDetails = $this->stylaPagesInteractor->getPageDetails($stylaPage);

        $response = $this->renderStorefront(
            '@StylaCmsIntegrationPlugin/storefront/styla_page/page.html.twig',
            [
                'page' => $page,
                'client' => $stylaPage->getAccountName(),
                'path' => ltrim($stylaPage->getPath(), '/'),
                'head' => $pageDetails->getHead(),
                'body' => $pageDetails->getBody(),
                'fullpath' => $stylaPage->getUseFullPath()
            ]
        );

        // The response header is read by Shopware's http cache store in addition to the collected tags.
        // It also covers responses rendered in a sub request (Styla pages for unknown URLs).
        self::addHttpCacheTag($response, $cacheTag);

        return $response;
    }

    private static function addHttpCacheTag(Response $response, string $tag): void
    {
        $tags = [];
        if ($response->headers->has(CacheStore::TAG_HEADER)) {
            $tags = json_decode((string) $response->headers->get(CacheStore::TAG_HEADER), true) ?: [];
        }
        $tags[] = $tag;

        $response->headers->set(CacheStore::TAG_HEADER, json_encode(array_values(array_unique($tags))));
    }
}
