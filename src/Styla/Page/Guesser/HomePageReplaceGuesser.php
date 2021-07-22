<?php

namespace Styla\CmsIntegration\Styla\Page\Guesser;

use Styla\CmsIntegration\Entity\StylaPage\StylaPage;
use Styla\CmsIntegration\Styla\Page\Guesser\DTO\ShopwarePageDetails;

class HomePageReplaceGuesser extends AbstractStylaPageToReplaceGuesser
{
    public function guessStylaPage(ShopwarePageDetails $shopwarePageDetails): ?StylaPage
    {
        if (!$this->isSupported($shopwarePageDetails)) {
            throw new \LogicException(
                sprintf('Page[path: %s] is not supported', $shopwarePageDetails->getDecodedPath())
            );
        }

        return $this->getStylaPageByPath('', $shopwarePageDetails->getContext());
    }

    public function isSupported(ShopwarePageDetails $shopwarePageDetails): bool
    {
        return $this->isHomePage($shopwarePageDetails);
    }

    private function isHomePage(ShopwarePageDetails $shopwarePageDetails): bool
    {
        return $shopwarePageDetails->getRoute() === 'frontend.home.page'
            || $shopwarePageDetails->getDecodedPath() === '/';
    }
}
