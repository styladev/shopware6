<?php

namespace Styla\CmsIntegration\Entity\StylaPage;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class StylaPage extends Entity
{
    use EntityIdTrait;

    protected ?string $name = null;
    protected ?string $accountName = null;
    protected ?string $domain = null;
    protected ?string $path = null;
    protected ?string $title = null;
    protected ?string $seoTitle = null;
    protected ?int $position = null;
    protected ?\DateTimeInterface $stylaUpdatedAt = null;
    protected ?bool $useFullPath = false;

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getAccountName(): ?string
    {
        return $this->accountName;
    }

    /**
     * @param string|null $accountName
     */
    public function setAccountName(?string $accountName): void
    {
        $this->accountName = $accountName;
    }

    /**
     * @return string|null
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * @param string|null $domain
     */
    public function setDomain(?string $domain): void
    {
        $this->domain = $domain;
    }

    /**
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @param string|null $path
     */
    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string|null
     */
    public function getSeoTitle(): ?string
    {
        return $this->seoTitle;
    }

    /**
     * @param string|null $seoTitle
     */
    public function setSeoTitle(?string $seoTitle): void
    {
        $this->seoTitle = $seoTitle;
    }

    /**
     * @return int|null
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * @param int|null $position
     */
    public function setPosition(?int $position): void
    {
        $this->position = $position;
    }

    public function getStylaUpdatedAt(): ?\DateTimeInterface
    {
        return $this->stylaUpdatedAt;
    }

    public function setStylaUpdatedAt(?\DateTimeInterface $stylaUpdatedAt): void
    {
        $this->stylaUpdatedAt = $stylaUpdatedAt;
    }

    public function setUseFullPath(?bool $useFullPath): void
    {
        $this->useFullPath = $useFullPath;
    }

    public function getUseFullPath(): bool
    {
        return $this->useFullPath;
    }
}
