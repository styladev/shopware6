<?php

namespace Styla\CmsIntegration\Styla\Client\DTO;

class PageDetails
{
    private string $head;
    private string $body;
    private ?int $expire;

    public function __construct(string $head, string $body, ?int $expire)
    {
        $this->head = $head;
        $this->body = $body;
        $this->expire = $expire;
    }

    /**
     * @return string
     */
    public function getHead(): string
    {
        return $this->head;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @return int|null
     */
    public function getExpire(): ?int
    {
        return $this->expire;
    }
}
