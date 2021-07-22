<?php

namespace Styla\CmsIntegration\Styla\Api\DTO\Category;

class CategoryInfo implements \JsonSerializable
{
    private string $id;
    private string $name;
    private CategoryInfoList $children;

    public function __construct(string $id, string $name, CategoryInfoList $children)
    {
        $this->id = $id;
        $this->name = $name;
        $this->children = $children;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getChildren(): CategoryInfoList
    {
        return $this->children;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'children' => $this->getChildren()
        ];
    }
}
