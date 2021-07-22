<?php

namespace Styla\CmsIntegration\Styla\Api\DTO\Product;

class ProductAttributeOption implements \JsonSerializable
{
    private string $id;
    private string $label;
    private ProductReferenceInfoList $products;

    public function __construct(string $id, string $label, ProductReferenceInfoList $products)
    {
        $this->id = $id;
        $this->label = $label;
        $this->products = $products;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getProducts(): ProductReferenceInfoList
    {
        return $this->products;
    }


    public function jsonSerialize()
    {
        return ['id' => $this->getId(), 'label' => $this->getLabel(), 'products' => $this->getProducts()];
    }
}
