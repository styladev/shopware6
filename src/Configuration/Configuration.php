<?php

namespace Styla\CmsIntegration\Configuration;

class Configuration implements ConfigurationInterface
{
    private string $defaultAccountName;
    private string $defaultDomainUrl;

    /**
     * @var string[]
     */
    private array $definedAccountNames;
    /**
     * @var string[]
     */
    private array $definedDomainUrls;

    /**
     * @var string[]
     */
    private array $accountNameByLanguageIdMap;
    /**
     * @var string[]
     */
    private array $domainUrlByLanguageIdMap;

    private int $pageDetailsCacheDuration;

    private int $pageListSynchronizationInterval;

    /**
     * @var array|string[]
     */
    private array $listOfExtraPagesAllowedToOverride;

    public function __construct(
        string $defaultAccountName,
        string $defaultDomainUrl,
        array $accountNameByLanguageIdMap,
        array $domainUrlByLanguageIdMap,
        int $pageDetailsCacheDuration,
        int $pageListSynchronizationInterval,
        array $listOfExtraPagesAllowedToOverride
    ) {
        $this->defaultAccountName = $defaultAccountName;
        $this->defaultDomainUrl = $defaultDomainUrl;
        $this->accountNameByLanguageIdMap = $accountNameByLanguageIdMap;
        $this->domainUrlByLanguageIdMap = $domainUrlByLanguageIdMap;

        $accountNameByLanguageIdMap[] = $defaultAccountName;
        $this->definedAccountNames = array_unique($accountNameByLanguageIdMap);

        $domainUrlByLanguageIdMap[] = $defaultDomainUrl;
        $this->definedDomainUrls = array_unique($domainUrlByLanguageIdMap);

        $this->pageDetailsCacheDuration = $pageDetailsCacheDuration;
        $this->pageListSynchronizationInterval = $pageListSynchronizationInterval;
        $this->listOfExtraPagesAllowedToOverride = $listOfExtraPagesAllowedToOverride;
    }

    /**
     * @return string[]
     */
    public function getDefinedAccountNames(): array
    {
        return $this->definedAccountNames;
    }

    /**
     * @return string[]
     */
    public function getDefinedDomainUrls(): array
    {
        return $this->definedDomainUrls;
    }

    /**
     * @return int
     */
    public function getPageDetailsCacheDuration(): int
    {
        return $this->pageDetailsCacheDuration;
    }

    /**
     * @return int
     */
    public function getPageListSynchronizationInterval(): int
    {
        return $this->pageListSynchronizationInterval;
    }

    /**
     * @return array|string[]
     */
    public function getListOfExtraPagesAllowedToOverride(): array
    {
        return $this->listOfExtraPagesAllowedToOverride;
    }

    public function getAccountNameByLanguage(string $languageId): string
    {
        foreach ($this->accountNameByLanguageIdMap as $accountLanguageId => $accountName) {
            if ($accountLanguageId === $languageId) {
                return $accountName;
            }
        }

        return $this->defaultAccountName;
    }
}
