<?php

namespace Styla\CmsIntegration\Styla\Page\Guesser;

use Styla\CmsIntegration\Entity\StylaPage\StylaPage;
use Styla\CmsIntegration\Styla\Page\Guesser\DTO\ShopwarePageDetails;

interface StylaPageToReplaceGuesserInterface
{
    /**
     * @param ShopwarePageDetails $shopwarePageDetails
     *
     * @return StylaPage|null Should return found StylaPage that matches ShopwarePageDetails
     * @throws \Throwable
     */
    public function guessStylaPage(ShopwarePageDetails $shopwarePageDetails): ?StylaPage;

    /**
     * Should return true if guesser can process this page
     */
    public function isSupported(ShopwarePageDetails $shopwarePageDetails): bool;
}
