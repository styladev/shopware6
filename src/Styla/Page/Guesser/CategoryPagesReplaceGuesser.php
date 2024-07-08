<?php

namespace Styla\CmsIntegration\Styla\Page\Guesser;

use Styla\CmsIntegration\Entity\StylaPage\StylaPage;
use Styla\CmsIntegration\Styla\Page\Guesser\DTO\ShopwarePageDetails;

class CategoryPagesReplaceGuesser extends AbstractStylaPageToReplaceGuesser
{
    private const CATEGORIES_PAGE_ROUTE = 'frontend.navigation.page';

    public function guessStylaPage(ShopwarePageDetails $shopwarePageDetails): ?StylaPage
    {
        $path = $shopwarePageDetails->getDecodedPath();
        if (!$this->isSupported($shopwarePageDetails)) {
            throw new \LogicException(
                sprintf('Page[path: %s] is not supported', $path)
            );
        }

        return $this->getStylaPageByPath(
            $path,
            $shopwarePageDetails->getContext()
        );
    }

    public function isSupported(ShopwarePageDetails $shopwarePageDetails): bool
    {
        return $this->isCategoryPage($shopwarePageDetails);
    }

    private function isCategoryPage(ShopwarePageDetails $shopwarePageDetails): bool
    {
        return $shopwarePageDetails->getRoute() === self::CATEGORIES_PAGE_ROUTE
            && $shopwarePageDetails->getDecodedPathBeforeShopwareRewrite() !== null;
    }
}
