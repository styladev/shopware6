<?php

namespace Styla\CmsIntegration\Configuration;

interface ConfigurationInterface
{
    /**
     * @return int
     */
    public function getPageDetailsCacheDuration(): int;

    /**
     * @return array|string[]
     */
    public function getListOfExtraPagesAllowedToOverride(): array;

    /**
     * @return int
     */
    public function getPageListSynchronizationInterval(): int;

    /**
     * @return string[]
     */
    public function getDefinedAccountNames(): array;

    /**
     * @param string $languageId
     *
     * @return string
     */
    public function getAccountNameByLanguage(string $languageId): string;
}
