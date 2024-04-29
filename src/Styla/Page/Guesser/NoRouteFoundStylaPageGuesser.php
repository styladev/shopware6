<?php

namespace Styla\CmsIntegration\Styla\Page\Guesser;

use Styla\CmsIntegration\Entity\StylaPage\StylaPage;
use Styla\CmsIntegration\Styla\Page\Guesser\DTO\ShopwarePageDetails;
use Styla\CmsIntegration\Routing\StylaUrlGenerator;

class NoRouteFoundStylaPageGuesser extends AbstractStylaPageToReplaceGuesser
{
    public function guessStylaPage(ShopwarePageDetails $shopwarePageDetails): ?StylaPage
    {
        return $this->getStylaPageByPath($shopwarePageDetails->getDecodedPath(), $shopwarePageDetails->getContext());
    }

    public function isSupported(ShopwarePageDetails $shopwarePageDetails): bool
    {
        return $shopwarePageDetails->getRoute() === null ||
            str_contains($shopwarePageDetails->getRoute(), StylaUrlGenerator::STYLA_CMS_PAGES_ROUTE_PREFIX);
    }
}
