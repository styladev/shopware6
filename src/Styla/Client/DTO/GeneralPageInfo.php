<?php

namespace Styla\CmsIntegration\Styla\Client\DTO;

class GeneralPageInfo
{
    private string $accountName;
    private string $name;
    private string $domain;
    private string $path;
    private string $type;
    private ?\DateTimeInterface $updatedAt;
    private ?\DateTimeInterface $deletedAt;
    private ?int $position;
    private string $title;
    private string $seoTitle;

    public function __construct(
        string $accountName,
        string $name,
        string $domain,
        string $path,
        string $type,
        ?\DateTimeInterface $updatedAt,
        ?\DateTimeInterface $deletedAt,
        ?int $position,
        string $title,
        string $seoTitle
    ) {
        $this->accountName = $accountName;
        $this->name = $name;
        $this->domain = $domain;
        $this->path = $path;
        $this->type = $type;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
        $this->position = $position;
        $this->title = $title;
        $this->seoTitle = $seoTitle;
    }

    public function getAccountName(): string
    {
        return $this->accountName;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    /**
     * @return int|null
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getSeoTitle(): string
    {
        return $this->seoTitle;
    }
}
