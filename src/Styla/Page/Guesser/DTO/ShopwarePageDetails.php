<?php

namespace Styla\CmsIntegration\Styla\Page\Guesser\DTO;

use Psr\Log\LoggerInterface;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\PlatformRequest;
use Shopware\Storefront\Framework\Routing\RequestTransformer;
use Symfony\Component\HttpFoundation\Request;

class ShopwarePageDetails implements \JsonSerializable
{
    private string $decodedPath;
    private ?string $decodedPathBeforeShopwareRewrite;
    private ?string $route;
    private Context $context;
    private Request $originalRequest;

    public function __construct(
        string $decodedPath,
        ?string $decodedPathBeforeShopwareRewrite,
        ?string $route,
        Context $context,
        Request $originalRequest
    ) {
        $this->decodedPath = $decodedPath;
        $this->decodedPathBeforeShopwareRewrite = $decodedPathBeforeShopwareRewrite;
        $this->route = $route;
        $this->context = $context;
        $this->originalRequest = $originalRequest;
    }

    public function getDecodedPath(): string
    {
        return $this->decodedPath;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function getDecodedPathBeforeShopwareRewrite(): ?string
    {
        return $this->decodedPathBeforeShopwareRewrite;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getOriginalRequest(): Request
    {
        return $this->originalRequest;
    }

    /**
     * @param Request $request
     * @param LoggerInterface $logger
     *
     * @return ShopwarePageDetails
     */
    public static function createFromRequest(Request $request, LoggerInterface $logger, ?bool $useFullPath = false): ?ShopwarePageDetails
    {
        try {
            $pathInfo = $request->getPathInfo();
            $decodedPath = urldecode($pathInfo);

            $pathBeforeShopwareRewrite = $request->get(RequestTransformer::ORIGINAL_REQUEST_URI);
            $decodedPathBeforeShopwareRewrite = urldecode($pathBeforeShopwareRewrite);

            // Shopware always take the sales channel domain url first
            // In that case path already sliced
            // so we need to use full path
            if ($useFullPath) {
                $decodedPath = $pathBeforeShopwareRewrite;
            }

            $route = $request->get('_route');

            $context = $request->get(PlatformRequest::ATTRIBUTE_CONTEXT_OBJECT);
            if (!$context) {
                $context = self::resolveContextFromRequest($request);
            }

            return new ShopwarePageDetails($decodedPath, $decodedPathBeforeShopwareRewrite, $route, $context, $request);
        } catch (\Throwable $exception) {
            $logger->error(
                'Could not convert request to page details',
                [
                    'exception' => $exception,
                    // Could not save request object to avoid store of the sensitive information in log file
                    'requestPath' => $request->getPathInfo()
                ]
            );

            return null;
        }
    }

    /**
     * @param Request $request
     *
     * @return Context
     */
    private static function resolveContextFromRequest(Request $request): Context
    {
        $swLanguage = $language = $request->headers->get(PlatformRequest::HEADER_LANGUAGE_ID);
        $languages = [Defaults::LANGUAGE_SYSTEM];
        if ($swLanguage) {
            if (trim($swLanguage)) {
                array_unshift($languages, $swLanguage);
            }
        }

        $context = new Context(
            new SystemSource(),
            [],
            Defaults::CURRENCY,
            $languages
        );

        return $context;
    }

    /**
     * This interface was implemented to support custom entity serialization into log file
     * Serialized data must not include whole request to avoid store of the sensitive data in log files
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $contextSource = $this->context->getSource();
        return [
            'decodedPath' => $this->decodedPath,
            'decodedPathBeforeShopwareRewrite' => $this->decodedPathBeforeShopwareRewrite,
            'route' => $this->route,
            'context' => [
                'languageIds' => $this->context->getLanguageIdChain(),
                'scope' => $this->context->getScope(),
                'sourceType' => get_class($contextSource)
            ],
            'request' => [
                'uri' => $this->originalRequest->getUri(),
                'salesChannelId' => $this->originalRequest
                    ->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID),
                'routeScope' => $this->originalRequest
                    ->attributes->get(PlatformRequest::ATTRIBUTE_ROUTE_SCOPE),
                'selectedLanguageId' => $this->originalRequest
                    ->attributes->get(PlatformRequest::HEADER_LANGUAGE_ID)
            ]
        ];
    }
}
