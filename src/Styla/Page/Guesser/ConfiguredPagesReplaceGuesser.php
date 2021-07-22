<?php

namespace Styla\CmsIntegration\Styla\Page\Guesser;

use Styla\CmsIntegration\Entity\StylaPage\StylaPage;
use Styla\CmsIntegration\Styla\Page\Guesser\DTO\ShopwarePageDetails;

class ConfiguredPagesReplaceGuesser extends AbstractStylaPageToReplaceGuesser
{
    public function guessStylaPage(ShopwarePageDetails $shopwarePageDetails): ?StylaPage
    {
        if (!$this->isSupported($shopwarePageDetails)) {
            throw new \LogicException(
                sprintf('Page[path: %s] is not supported', $shopwarePageDetails->getDecodedPath())
            );
        }

        return $this->getStylaPageByPath($shopwarePageDetails->getDecodedPath(), $shopwarePageDetails->getContext());
    }

    public function isSupported(ShopwarePageDetails $shopwarePageDetails): bool
    {
        return $this->isConfiguredPage($shopwarePageDetails);
    }

    public function isConfiguredPage(ShopwarePageDetails $shopwarePageDetails): bool
    {
        foreach ($this->configuration->getListOfExtraPagesAllowedToOverride() as $page) {
            if ($shopwarePageDetails->getDecodedPath() === $page) {
                return true;
            }
        }

        return false;
    }
}
