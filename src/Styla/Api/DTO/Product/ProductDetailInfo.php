<?php

namespace Styla\CmsIntegration\Styla\Api\DTO\Product;

class ProductDetailInfo extends ProductReferenceInfo
{
    private const TYPE_SIMPLE = 'simple';
    private const TYPE_CONFIGURABLE = 'configurable';

    private string $type;
    private string $name;
    private string $priceTemplate;
    private ?float $minQty;
    private ?float $maxQty;
    private string $description;
    private string $shippingStatus;
    private string $brand;
    private array $categories;
    private ?TaxInfo $taxInfo;
    private ProductAttributeInfoList $attributes;

    public function __construct(
        string $id,
        bool $saleable,
        ?float $price,
        ?float $oldPrice,
        string $name,
        string $priceTemplate,
        ?float $minQty,
        ?float $maxQty,
        string $description,
        string $shippingStatus,
        string $brand,
        array $categories,
        ?TaxInfo $taxInfo,
        ProductAttributeInfoList $attributes
    ) {
        parent::__construct($id, $saleable, $price, $oldPrice);

        $this->name = $name;
        $this->priceTemplate = $priceTemplate;
        $this->minQty = $minQty;
        $this->maxQty = $maxQty;
        $this->description = $description;
        $this->shippingStatus = $shippingStatus;
        $this->brand = $brand;
        $this->categories = $categories;
        $this->taxInfo = $taxInfo;
        $this->attributes = $attributes;

        $this->type = $attributes->count() > 0 ? self::TYPE_CONFIGURABLE : self::TYPE_SIMPLE;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPriceTemplate(): string
    {
        return $this->priceTemplate;
    }

    public function getMinQty(): ?float
    {
        return $this->minQty;
    }

    public function getMaxQty(): ?float
    {
        return $this->maxQty;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getShippingStatus(): string
    {
        return $this->shippingStatus;
    }

    public function getBrand(): string
    {
        return $this->brand;
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function getTaxInfo(): ?TaxInfo
    {
        return $this->taxInfo;
    }

    public function getAttributes(): ProductAttributeInfoList
    {
        return $this->attributes;
    }

    public function jsonSerialize()
    {
        $serializedReference = parent::jsonSerialize();

        $serializedReference['type'] = $this->getType();
        $serializedReference['name'] = $this->getName();
        $serializedReference['priceTemplate'] = $this->getPriceTemplate();
        $serializedReference['minqty'] = $this->getMinQty();
        $serializedReference['maxqty'] = $this->getMaxQty();
        $serializedReference['description'] = $this->getDescription();
        $serializedReference['shippingStatus'] = $this->getShippingStatus();
        $serializedReference['brand'] = $this->getBrand();
        $serializedReference['categories'] = $this->getCategories();
        $serializedReference['tax'] = $this->getTaxInfo();
        $serializedReference['attributes'] = $this->getAttributes();

        return $serializedReference;
    }
}
