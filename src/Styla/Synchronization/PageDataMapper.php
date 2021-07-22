<?php

namespace Styla\CmsIntegration\Styla\Synchronization;

use Styla\CmsIntegration\Entity\StylaPage\StylaPage;
use Styla\CmsIntegration\Styla\Client\DTO\GeneralPageInfo;

class PageDataMapper
{
    /**
     * @param GeneralPageInfo $from
     * @param StylaPage $to
     *
     * @return array List of new/updated values in the format
     *               of the StylaPage Shopware repository "insert/update" method
     */
    public function map(GeneralPageInfo $from, StylaPage $to): array
    {
        if (!$to->getId()) {
            return $this->mapToNewEntity($from, $to);
        } else {
            return $this->mapToExistingEntity($from, $to);
        }
    }

    private function mapToNewEntity(GeneralPageInfo $from, StylaPage $to): array
    {
        $to->setStylaUpdatedAt($from->getUpdatedAt());
        $to->setPosition($from->getPosition());
        $to->setSeoTitle($from->getSeoTitle());
        $to->setTitle($from->getTitle());
        $to->setDomain($from->getDomain());
        $to->setName($from->getName());
        $to->setPath($from->getPath());
        $to->setAccountName($from->getAccountName());

        return [
            'name' => $from->getName(),
            'domain' => $from->getDomain(),
            'title' => $from->getTitle(),
            'seoTitle' => $from->getSeoTitle(),
            'position' => $from->getPosition(),
            'stylaUpdatedAt' => $from->getUpdatedAt(),
            'path' => $from->getPath(),
            'accountName' => $from->getAccountName()
        ];
    }

    private function mapToExistingEntity(GeneralPageInfo $from, StylaPage $to): array
    {
        $changedData = [];
        // !== is not used because StylaPage properties: "name", "domain", "title", "seoTitle" could be null
        if ($to->getName() != $from->getName()) {
            $changedData['name'] = $from->getName();
            $to->setName($from->getName());
        }
        if ($to->getDomain() != $from->getDomain()) {
            $changedData['domain'] = $from->getDomain();
            $to->setDomain($from->getDomain());
        }
        if ($to->getTitle() != $from->getTitle()) {
            $changedData['title'] = $from->getTitle();
            $to->setTitle($from->getTitle());
        }
        if ($to->getSeoTitle() != $from->getSeoTitle()) {
            $changedData['seoTitle'] = $from->getSeoTitle();
            $to->setSeoTitle($from->getSeoTitle());
        }
        if ($to->getPosition() !== $from->getPosition()) {
            $changedData['position'] = $from->getPosition();
            $to->setPosition($from->getPosition());
        }

        $existingEntityFormattedUpdatedAt = $to->getStylaUpdatedAt()
            ? $to->getStylaUpdatedAt()->getTimestamp()
            : null;
        $stylaPageInfoFormattedUpdatedAt = $from->getUpdatedAt()
            ? $from->getUpdatedAt()->getTimestamp()
            : null;
        if ($existingEntityFormattedUpdatedAt !== $stylaPageInfoFormattedUpdatedAt) {
            $changedData['stylaUpdatedAt'] = $from->getUpdatedAt();
            $to->setStylaUpdatedAt($from->getUpdatedAt());
        }

        return $changedData;
    }
}
