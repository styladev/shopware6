<?php

namespace Styla\CmsIntegration\Routing;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Storefront\Framework\Routing\RequestTransformer;
use Styla\CmsIntegration\Entity\StylaPage\StylaPage;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\ConfigurableRequirementsInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

class StylaUrlGenerator implements UrlGeneratorInterface, ConfigurableRequirementsInterface
{
    public const STYLA_CMS_PAGES_ROUTE_PREFIX = 'styla.cms_integration.page_route.';

    /**
     * @var UrlGeneratorInterface|ConfigurableRequirementsInterface
     */
    private UrlGeneratorInterface $innerGenerator;
    private RequestStack $requestStack;
    private EntityRepository $stylaPageRepository;
    private LoggerInterface $logger;

    public function __construct(
        $innerGenerator,
        RequestStack $requestStack,
        EntityRepository $stylaPageRepository,
        LoggerInterface $logger
    ) {
        $this->innerGenerator = $innerGenerator;
        $this->requestStack = $requestStack;
        $this->stylaPageRepository = $stylaPageRepository;
        $this->logger = $logger;
    }

    public function setStrictRequirements(?bool $enabled)
    {
        $this->innerGenerator->setStrictRequirements($enabled);
    }

    public function isStrictRequirements(): ?bool
    {
        return $this->innerGenerator->isStrictRequirements();
    }

    public function setContext(RequestContext $context)
    {
        $this->innerGenerator->setContext($context);
    }

    public function getContext(): RequestContext
    {
        return $this->innerGenerator->getContext();
    }

    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        $implementedReferenceTypes = [
            self::ABSOLUTE_URL,
            self::ABSOLUTE_PATH,
        ];

        if (strpos($name, self::STYLA_CMS_PAGES_ROUTE_PREFIX) === 0
            && in_array($referenceType, $implementedReferenceTypes)) {
            $pageIdentifier = str_replace(self::STYLA_CMS_PAGES_ROUTE_PREFIX, '', $name);

            return $this->generateStylaPageRoute($pageIdentifier, $referenceType);
        }

        return $this->innerGenerator->generate($name, $parameters, $referenceType);
    }

    private function generateStylaPageRoute(string $pageIdentifier, $referenceType): string
    {
        try {
            $page = $this->getPage($pageIdentifier);

            switch ($referenceType) {
                case self::ABSOLUTE_URL:
                    return $this->generateAbsoluteUrl($page->getPath());
                default:
                    return $page->getPath();
            }
        } catch (\Throwable $exception) {
            $message = sprintf(
                'Unable to generate a URL for the styla cms page[id=%s] as such route does not exist.',
                $pageIdentifier
            );
            $this->logger->error(
                $message,
                [
                    'exception' => $exception,
                    'pageIdentifier' => $pageIdentifier,
                    'referenceType' => $referenceType,
                ]
            );

            throw new RouteNotFoundException($message, 0, $exception);
        }
    }

    private function getPage(string $id): ?StylaPage
    {
        return $this->stylaPageRepository
            ->search(new Criteria([$id]), Context::createDefaultContext())->first();
    }

    private function generateAbsoluteUrl(string $pagePath)
    {
        $request = $this->requestStack->getMasterRequest();
        $basePath = '';
        $salesChannelBaseUrl = '';
        if ($request) {
            $basePath = $request->getBasePath();
            $salesChannelBaseUrl = (string)$request->attributes->get(RequestTransformer::SALES_CHANNEL_BASE_URL);
        }

        $schema = $this->getContext()->getScheme() . ':';
        $schemaAuthority = $schema . '//' . $this->getContext()->getHost();

        if ($this->getContext()->getHttpPort() !== 80) {
            $schemaAuthority .= ':' . $this->getContext()->getHttpPort();
        } elseif ($this->getContext()->getHttpsPort() !== 443) {
            $schemaAuthority .= ':' . $this->getContext()->getHttpsPort();
        }

        if ($salesChannelBaseUrl && $basePath) {
            return sprintf(
                '%s/%s/%s/%s',
                $schemaAuthority,
                trim($basePath, '/'),
                trim($salesChannelBaseUrl, '/'),
                ltrim($pagePath, '/')
            );
        } elseif ($basePath || $salesChannelBaseUrl) {
            return sprintf(
                '%s/%s/%s',
                $schemaAuthority,
                $basePath ? trim($basePath, '/') : trim($salesChannelBaseUrl, '/'),
                ltrim($pagePath, '/')
            );
        } else {
            return sprintf('%s/%s', $schemaAuthority, ltrim($pagePath, '/'));
        }
    }
}
