<?php

namespace Styla\CmsIntegration\Configuration;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Styla\CmsIntegration\Exception\InvalidConfigurationException;

class ConfigurationFactory
{
    public const PREFIX = 'StylaCmsIntegration.settings.';
    public const PAGES_LIST_SYNCHRONIZATION_INTERVAL_CONFIG_KEY = self::PREFIX . 'pagesListSynchronizationInterval';

    private SystemConfigService $systemConfigService;

    public function __construct(SystemConfigService $systemConfigService)
    {
        $this->systemConfigService = $systemConfigService;
    }

    public function createConfigurationForCurrentContext(): ConfigurationInterface
    {
        $accountNamesHashMap = $this->systemConfigService->get(self::PREFIX . 'accountNames');
        $domainUrlsHashMap = $this->systemConfigService->get(self::PREFIX . 'domainUrls');
        $defaultAccountName = trim($this->systemConfigService->getString(self::PREFIX . 'defaultAccountName'));
        $defaultDomainUrl = trim($this->systemConfigService->getString(self::PREFIX . 'defaultDomainUrl'));

        if ($defaultAccountName === '') {
            throw new InvalidConfigurationException(
                'Styla CMS Integration Default Account Name configuration is not defined'
            );
        }

        $accountNameByLanguageIdMap = [];
        foreach ($accountNamesHashMap as $languageId => $accountName) {
            if (!$accountName || trim($accountName) === '') {
                continue;
            }

            $accountNameByLanguageIdMap[$languageId] =  $accountName ? $accountName : '';
        }

        $domainUrlByLanguageIdMap = [];
        foreach ($domainUrlsHashMap as $languageId => $domainUrl) {
            if (!$domainUrl || trim($domainUrl) === '') {
                continue;
            }

            $domainUrlByLanguageIdMap[$languageId] = $domainUrl ? $domainUrl : '';
        }

        $pageDetailsCacheDuration = $this->systemConfigService->getInt(self::PREFIX . 'pageCacheDuration');
        if (!$pageDetailsCacheDuration) {
            throw new InvalidConfigurationException(
                'Styla CMS Integration Page details Cache Duration configuration is not defined'
            );
        }

        $pagesListSynchronizationInterval = $this->systemConfigService
            ->getString(self::PAGES_LIST_SYNCHRONIZATION_INTERVAL_CONFIG_KEY);
        if (!$pagesListSynchronizationInterval) {
            throw new InvalidConfigurationException(
                'Styla CMS Integration Page list synchronization interval configuration is not defined'
            );
        }

        $listOfExtraPagesToOverride = [];
        $listOfExtraPagesToOverrideString = $this->systemConfigService
            ->getString(self::PREFIX . 'extraPagesAllowedToOverride');
        if ($listOfExtraPagesToOverrideString) {
            $listOfExtraPagesToOverride = explode("\n", $listOfExtraPagesToOverrideString);
            $listOfExtraPagesToOverride = array_map('trim', $listOfExtraPagesToOverride);
        }

        return new Configuration(
            $defaultAccountName,
            $defaultDomainUrl,
            $accountNameByLanguageIdMap,
            $domainUrlByLanguageIdMap,
            $pageDetailsCacheDuration,
            $pagesListSynchronizationInterval,
            $listOfExtraPagesToOverride
        );
    }
}
