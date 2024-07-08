<?php

namespace Styla\CmsIntegration\EventSubscriber;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Routing\AbstractRouteScope;
use Shopware\Storefront\Framework\Routing\StorefrontRouteScope;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\PlatformRequest;
use Styla\CmsIntegration\Controller\Storefront\StylaPageController;
use Styla\CmsIntegration\Entity\StylaPage\StylaPage;
use Styla\CmsIntegration\Routing\StylaUrlGenerator;
use Styla\CmsIntegration\Styla\Page\Guesser\DTO\ShopwarePageDetails;
use Styla\CmsIntegration\UseCase\StylaPagesInteractor;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Styla\CmsIntegration\Configuration\ConfigurationFactory;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class StorefrontRequestControllerSubstituteEventSubscriber implements EventSubscriberInterface
{
    const STYLA_PAGE_INSTANCE_ARGUMENT = '_styla_cms_page_instance';

    private StylaPagesInteractor $stylaPagesInteractor;
    private StylaPageController $stylaPageController;
    private LoggerInterface $logger;
    private SystemConfigService $systemConfigService;
    private bool $useFullPath = false;

    public function __construct(
        StylaPagesInteractor $stylaPagesInteractor,
        StylaPageController $stylaPageController,
        LoggerInterface $logger,
        SystemConfigService $systemConfigService
    ) {
        $this->stylaPagesInteractor = $stylaPagesInteractor;
        $this->stylaPageController = $stylaPageController;
        $this->logger = $logger;
        $this->systemConfigService = $systemConfigService;
        $this->useFullPath = $this->systemConfigService->getBool(ConfigurationFactory::PREFIX.'useFullPath') || false;
    }

    public function resolveControllerArguments(ControllerArgumentsEvent $controllerArgumentsEvent)
    {
        $request = $controllerArgumentsEvent->getRequest();
        if (!$request->attributes->has(PlatformRequest::ATTRIBUTE_ROUTE_SCOPE)) {
            $request->attributes->set(PlatformRequest::ATTRIBUTE_ROUTE_SCOPE, [StorefrontRouteScope::ID]);
        }
        // Disable functionality for any scope except storefront
        if ($this->isUnsupportedScope($request)
            || $controllerArgumentsEvent->getRequestType() !== HttpKernelInterface::MASTER_REQUEST
            || strpos($request->getPathInfo(), '/admin') === 0) {
            return;
        }

        $shopwarePageDetails = ShopwarePageDetails::createFromRequest($request, $this->logger, $this->useFullPath);
        if ($shopwarePageDetails === null) {
            return;
        }
        $stylaPage = $this->stylaPagesInteractor->guessPageToReplaceShopwarePage($shopwarePageDetails);
        if (!$stylaPage) {
            return;
        }
        $stylaPage->setUseFullPath($this->useFullPath);

        $salesChannelContext = null;
        foreach ($controllerArgumentsEvent->getArguments() as $argument) {
            if ($argument instanceof SalesChannelContext) {
                $salesChannelContext = $argument;
            }
        }
        if ($salesChannelContext === null) {
            $this->logger->error(
                sprintf(
                    'Sales channel context was not found for page[path: %s]',
                    $request->getPathInfo()
                )
            );

            return;
        }

        $previousController = $controllerArgumentsEvent->getController();
        $previousArguments = $controllerArgumentsEvent->getArguments();
        $controllerDelegate = function (StylaPage $stylaPage, Request $request, SalesChannelContext $context) use (
            $previousController,
            $previousArguments
        ) {
            try {
                return $this->stylaPageController->renderStylaPage($stylaPage, $request, $context);
            } catch (\Throwable $exception) {
                return $previousController(...$previousArguments);
            }
        };
        $controllerArgumentsEvent->setController($controllerDelegate);

        $controllerArgumentsEvent->setArguments(
            [
                $stylaPage,
                $request,
                $salesChannelContext
            ]
        );
    }

    public function onKernelException(ExceptionEvent $event)
    {
        if (
            !$event->getThrowable() instanceof NotFoundHttpException &&
            !$event->getThrowable() instanceof \Shopware\Core\Content\Category\Exception\CategoryNotFoundException
        ) {
            return;
        }

        // Disable functionality for any scope except storefront
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST ||
            strpos($event->getRequest()->getPathInfo(), '/admin') === 0) {
            return;
        }

        $shopwarePageDetails = ShopwarePageDetails::createFromRequest($event->getRequest(), $this->logger, $this->useFullPath);
        if ($shopwarePageDetails === null) {
            return;
        }
        $stylaPage = $this->stylaPagesInteractor->guessPageToReplaceShopwarePage($shopwarePageDetails);
        if (!$stylaPage) {
            return;
        }
        $stylaPage->setUseFullPath($this->useFullPath);

        try {
            $request = $this->duplicateRequest($event->getRequest(), $stylaPage);
            $response = $event->getKernel()
                ->handle($request, HttpKernelInterface::SUB_REQUEST, false);
            $event->allowCustomResponseCode();

            $this->updateMasterRequest($event->getRequest(), $request);

            $event->setResponse($response);
        } catch (\Throwable $e) {
            $this->logger->error(
                sprintf(
                    'Could not render styla page[path: %s]',
                    $event->getRequest()->getPathInfo()
                )
            );
        }
    }

    protected function duplicateRequest(Request $request, StylaPage $stylaPage): Request
    {
        // previous attributes should be left to avoid problems with the context resolving
        $previousAttributes = $request->attributes->all();

        // override controller related attributes
        $previousAttributes['_controller'] = sprintf('%s::%s', StylaPageController::class, 'renderStylaPage');
        // Added to avoid problems with redirects to this page
        $previousAttributes['_route'] = StylaUrlGenerator::STYLA_CMS_PAGES_ROUTE_PREFIX . $stylaPage->getId();
        // Force storefront route scope as we never hit the controller to get this route scope
        if (!isset($previousAttributes[PlatformRequest::ATTRIBUTE_ROUTE_SCOPE])) {
            $previousAttributes[PlatformRequest::ATTRIBUTE_ROUTE_SCOPE] = [StorefrontRouteScope::ID];
        }
        $previousAttributes[self::STYLA_PAGE_INSTANCE_ARGUMENT] = $stylaPage;
        // Modify resolved-uri
        if (isset($previousAttributes['resolved-uri'])) {
            $previousAttributes['resolved-uri'] = $stylaPage->path;
        }
        // Remove unused attributes for styla page
        if (isset($previousAttributes['navigationId'])) {
            unset($previousAttributes['navigationId']);
        }
        if (isset($previousAttributes['_route_params'])) {
            unset($previousAttributes['_route_params']);
        }
        if (isset($previousAttributes['sw-context'])) {
            unset($previousAttributes['sw-context']);
        }
        if (isset($previousAttributes['sw-sales-channel-context'])) {
            unset($previousAttributes['sw-sales-channel-context']);
        }
        if (isset($previousAttributes['_httpCache'])) {
            unset($previousAttributes['_httpCache']);
        }
        if (isset($previousAttributes['_cspNonce'])) {
            unset($previousAttributes['_cspNonce']);
        }

        $request = $request->duplicate(null, null, $previousAttributes);

        $request->setMethod('GET');

        return $request;
    }

    /**
     * Update original request to avoid problems with the listener on KernelEvents::RESPONSE
     * and KernelEvents::FINISH_REQUEST for case when custom response is created on page not found error
     *
     * @param Request $masterRequest
     * @param Request $stylaRequest
     */
    public function updateMasterRequest(Request $masterRequest, Request $stylaRequest)
    {
        // map all not frozen bags
        $masterRequest->attributes = $stylaRequest->attributes;
        $masterRequest->headers = $stylaRequest->headers;
        $masterRequest->server = $stylaRequest->server;

        $masterRequest->request = $stylaRequest->request;
        $masterRequest->cookies = $stylaRequest->cookies;
        $masterRequest->files = $stylaRequest->files;
    }

    public function isUnsupportedScope(Request $request): bool
    {
        $scope = $request->attributes->get('_routeScope');
        if ($scope instanceof AbstractRouteScope) {
            return !$scope->hasScope('storefront');
        }

        /**
         * Divided because actually they does not implement the same
         * interface and method names are the same by coincidence
         */
        if ($scope instanceof RouteScope) {
            return !$scope->hasScope('storefront');
        }

        /**
         * When scope is just a string in array
         */
        if (in_array(StorefrontRouteScope::ID, $scope, true)) {
            return false;
        }

        return true;
    }

    public static function getSubscribedEvents()
    {
        // Priority: the highest means executed first
        // Exception event by default actually going last
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => ['resolveControllerArguments', -1000],
            KernelEvents::EXCEPTION => [
                /**
                 * Should be called before logger interaction or any other activity to check if no route found is
                 * normal situation when styla page is created without related shopware page
                 */
                ['onKernelException', 1000],
            ],
        ];
    }
}
