<?php

namespace Styla\CmsIntegration\Styla\Page\Guesser;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Styla\CmsIntegration\Configuration\ConfigurationInterface;

abstract class AbstractStylaPageToReplaceGuesser implements StylaPageToReplaceGuesserInterface
{
    protected EntityRepository $stylaPagesRepository;
    protected ConfigurationInterface $configuration;

    public function __construct(EntityRepository $stylaPagesRepository, ConfigurationInterface $configuration)
    {
        $this->stylaPagesRepository = $stylaPagesRepository;
        $this->configuration = $configuration;
    }

    protected function getStylaPageByPath(string $path, Context $context)
    {
        $criteria = new Criteria();

        $accountName = $this->configuration->getAccountNameByLanguage($context->getLanguageId());
        $criteria->addFilter(
            new AndFilter(
                [
                    new EqualsAnyFilter('path', self::getPathCandidates($path)),
                    new EqualsFilter('accountName', $accountName)
                ]
            )
        );
        $pages = $this->stylaPagesRepository->search($criteria, $context)->getEntities();

        // An exact path match wins over a match that only differs by the trailing slash
        foreach ($pages as $page) {
            if ($page->getPath() === $path) {
                return $page;
            }
        }

        return $pages->first();
    }

    /**
     * Shopware SEO URLs usually end with a slash (e.g. "/Clothing/") while Styla page paths do not ("/Clothing"),
     * so a path also matches with the trailing slash added or removed. The home page ("" or "/") is left as is.
     *
     * @return list<string>
     */
    public static function getPathCandidates(string $path): array
    {
        if ($path === '' || $path === '/') {
            return [$path];
        }

        $withoutSlash = rtrim($path, '/');
        if ($withoutSlash === '') {
            // e.g. "//": never fall back to the home page
            return [$path];
        }

        return array_values(array_unique([$path, $withoutSlash, $withoutSlash . '/']));
    }
}
