<?php

namespace Styla\CmsIntegration\Styla\Page\Guesser;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Styla\CmsIntegration\Configuration\ConfigurationInterface;

abstract class AbstractStylaPageToReplaceGuesser implements StylaPageToReplaceGuesserInterface
{
    protected EntityRepositoryInterface $stylaPagesRepository;
    protected ConfigurationInterface $configuration;

    public function __construct(EntityRepositoryInterface $stylaPagesRepository, ConfigurationInterface $configuration)
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
                    new EqualsFilter('path', $path),
                    new EqualsFilter('accountName', $accountName)
                ]
            )
        );
        $pagesSearchResult = $this->stylaPagesRepository->search($criteria, $context);

        return $pagesSearchResult->first();
    }
}
