<?php

namespace Styla\CmsIntegration\Configuration;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Styla\CmsIntegration\Exception\InvalidConfigurationException;

class ConfigurationFactory
{
    public const PAGES_LIST_SYNCHRONIZATION_INTERVAL_CONFIG_KEY =
        'StylaCmsIntegrationPlugin.config.pagesListSynchronizationInterval';

    private SystemConfigService $systemConfigService;

    public function __construct(SystemConfigService $systemConfigService)
    {
        $this->systemConfigService = $systemConfigService;
    }

    public function createConfigurationForCurrentContext(): ConfigurationInterface
    {
        $accountNamesHashMap = $this->systemConfigService->get('StylaCmsIntegrationPlugin.config.accountNames');
        if (!$accountNamesHashMap) {
            throw new InvalidConfigurationException(
                'Styla CMS Integration Account Names configuration is not defined'
            );
        }

        if (!isset($accountNamesHashMap['default']) || trim($accountNamesHashMap['default']) === '') {
            throw new InvalidConfigurationException(
                'Styla CMS Integration Default Account Name configuration is not defined'
            );
        }
        $defaultAccountName = trim($accountNamesHashMap['default']);
        unset($accountNamesHashMap['default']);

        $accountNameByLanguageIdMap = [];
        foreach ($accountNamesHashMap as $languageId => $accountName) {
            if (!$accountName || trim($accountName) === '') {
                continue;
            }

            $accountNameByLanguageIdMap[$languageId] = $accountName;
        }

        $pageDetailsCacheDuration = $this->systemConfigService
            ->getString('StylaCmsIntegrationPlugin.config.pageCacheDuration');
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
            ->getString('StylaCmsIntegrationPlugin.config.extraPagesAllowedToOverride');
        if ($listOfExtraPagesToOverrideString) {
            $listOfExtraPagesToOverride = explode("\n", $listOfExtraPagesToOverrideString);
            $listOfExtraPagesToOverride = array_map('trim', $listOfExtraPagesToOverride);
        }

        return new Configuration(
            $defaultAccountName,
            $accountNameByLanguageIdMap,
            $pageDetailsCacheDuration,
            $pagesListSynchronizationInterval,
            $listOfExtraPagesToOverride
        );
    }
}
