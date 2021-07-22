<?php

namespace Styla\CmsIntegration\Configuration;

class Configuration implements ConfigurationInterface
{
    private string $defaultAccountName;

    /**
     * @var string[]
     */
    private array $definedAccountNames;

    /**
     * @var string[]
     */
    private array $accountNameByLanguageIdMap;

    private int $pageDetailsCacheDuration;

    private int $pageListSynchronizationInterval;

    /**
     * @var array|string[]
     */
    private array $listOfExtraPagesAllowedToOverride;

    public function __construct(
        string $defaultAccountName,
        array $accountNameByLanguageIdMap,
        int $pageDetailsCacheDuration,
        int $pageListSynchronizationInterval,
        array $listOfExtraPagesAllowedToOverride
    ) {
        $this->defaultAccountName = $defaultAccountName;
        $this->accountNameByLanguageIdMap = $accountNameByLanguageIdMap;

        $accountNameByLanguageIdMap[] = $defaultAccountName;
        $this->definedAccountNames = array_unique($accountNameByLanguageIdMap);

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
