<?php

namespace Styla\CmsIntegration\Styla\Page\Guesser;

use Styla\CmsIntegration\Entity\StylaPage\StylaPage;
use Styla\CmsIntegration\Styla\Page\Guesser\DTO\ShopwarePageDetails;

class VirtualStylaPageToReplaceGuesser implements StylaPageToReplaceGuesserInterface
{
    /**
     * @var array|StylaPageToReplaceGuesserInterface[]
     */
    private array $guessers;

    public function __construct(iterable $guessers)
    {
        foreach ($guessers as $guesser) {
            $this->addGuesser($guesser);
        }
    }

    public function guessStylaPage(ShopwarePageDetails $shopwarePageDetails): ?StylaPage
    {
        foreach ($this->guessers as $guesser) {
            if ($guesser->isSupported($shopwarePageDetails)) {
                return $guesser->guessStylaPage($shopwarePageDetails);
            }
        }

        throw new \LogicException(
            sprintf('Page[path: %s] is not supported', $shopwarePageDetails->getDecodedPath())
        );
    }

    public function addGuesser(StylaPageToReplaceGuesserInterface $guesser)
    {
        $this->guessers[] = $guesser;
    }

    public function isSupported(ShopwarePageDetails $shopwarePageDetails): bool
    {
        foreach ($this->guessers as $guesser) {
            if ($guesser->isSupported($shopwarePageDetails)) {
                return true;
            }
        }

        return false;
    }
}
