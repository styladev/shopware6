<?php

namespace Styla\CmsIntegration\Styla\Api\DTO\Product;

class ProductInfo implements \JsonSerializable
{
    private string $id;
    private string $caption;

    /**
     * @var string[]
     */
    private array $images;
    private string $pageUrl;

    public function __construct(string $id, string $caption, array $images, string $pageUrl)
    {
        $this->id = $id;
        $this->caption = $caption;
        $this->images = $images;
        $this->pageUrl = $pageUrl;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCaption(): string
    {
        return $this->caption;
    }

    public function getImages(): array
    {
        return $this->images;
    }

    public function getPageUrl(): string
    {
        return $this->pageUrl;
    }


    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'caption' => $this->getCaption(),
            'images' => $this->getImages(),
            'pageURL' => $this->getPageUrl(),
        ];
    }
}
