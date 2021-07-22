<?php

namespace Styla\CmsIntegration\Styla\Api\DTO\Product;

class ProductAttributeInfo implements \JsonSerializable
{
    private string $id;
    private string $label;
    private ProductAttributeOptionsList $options;

    public function __construct(string $id, string $label, ProductAttributeOptionsList $options)
    {
        $this->id = $id;
        $this->label = $label;
        $this->options = $options;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getOptions(): ProductAttributeOptionsList
    {
        return $this->options;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'options' => $this->getOptions(),
        ];
    }
}
