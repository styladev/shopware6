<?php

namespace Styla\CmsIntegration\Styla\Api\DTO\Product;

class TaxInfo implements \JsonSerializable
{
    private float $rate;
    private bool $taxIncluded;
    private bool $showLabel;
    private string $label;

    public function __construct(float $rate, bool $taxIncluded, bool $showLabel, string $label)
    {
        $this->rate = $rate;
        $this->taxIncluded = $taxIncluded;
        $this->showLabel = $showLabel;
        $this->label = $label;
    }

    public function getRate(): float
    {
        return $this->rate;
    }

    public function isTaxIncluded(): bool
    {
        return $this->taxIncluded;
    }

    public function isShowLabel(): bool
    {
        return $this->showLabel;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function jsonSerialize()
    {
        return [
            "rate" => $this->getRate(),
            "taxIncluded" => $this->isTaxIncluded(),
            "showLabel" => $this->isShowLabel(),
            "label" => $this->getLabel()
        ];
    }
}
