<?php

namespace Styla\CmsIntegration\Twig\Extension;

use Psr\Cache\InvalidArgumentException;
use Shopware\Core\Framework\Context;
use Styla\CmsIntegration\Service\GetStylaModularContentService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class StylaSlotCacheTwigExtension extends AbstractExtension
{

    private GetStylaModularContentService $slotCacheService;

    public function __construct(GetStylaModularContentService $slotCacheService)
    {
        $this->slotCacheService = $slotCacheService;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('get_styla_cached_content', [$this, 'getStylaCachedContent']),
        ];
    }

    public function getStylaCachedContent(Context $context, $id)
    {

        try {
            return $this->slotCacheService->execute($context, $id);
        } catch (InvalidArgumentException $e) {

        }

        return false;
    }
}
