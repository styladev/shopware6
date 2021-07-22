<?php

namespace Styla\CmsIntegration\Styla\Api\DTO\Product;

class ProductReferenceInfo implements \JsonSerializable
{
    private string $id;
    private bool $saleable;
    private ?float $price;
    private ?float $oldPrice;

    public function __construct(string $id, bool $saleable, ?float $price, ?float $oldPrice)
    {
        $this->id = $id;
        $this->saleable = $saleable;
        $this->price = $price;
        $this->oldPrice = $oldPrice;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function isSaleable(): bool
    {
        return $this->saleable;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function getOldPrice(): ?float
    {
        return $this->oldPrice;
    }

    public function jsonSerialize()
    {
        return [
            "id" => $this->getId(),
            "saleable" => $this->isSaleable(),
            "price" => $this->getPrice(),
            "oldPrice" => $this->getOldPrice()
        ];
    }
}
