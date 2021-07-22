<?php

namespace Styla\CmsIntegration\Test\Constraint;

use PHPUnit\Framework\Assert;
use Styla\CmsIntegration\Entity\StylaPage\StylaPage;

class StylaPageMatchConstraint extends AbstractMultipleAssertConstraint
{
    private const NAME = 'name';
    private const ACCOUNT_NAME = 'accountName';
    private const DOMAIN = 'domain';
    private const PATH = 'path';
    private const TITLE = 'title';
    private const SEO_TITLE = 'seoTitle';
    private const POSITION = 'position';
    private const STYLA_UPDATED_AT = 'stylaUpdatedAt';

    private ComparableRepresentationDataConverter $comparableRepresentationDataConverter;

    private ?string $name;
    private ?string $accountName;
    private ?string $domain;
    private ?string $path;
    private ?string $title;
    private ?string $seoTitle;
    private ?int $position;
    protected ?\DateTimeInterface $stylaUpdatedAt = null;

    public function __construct(
        ?string $name,
        ?string $accountName,
        ?string $domain,
        ?string $path,
        ?string $title,
        ?string $seoTitle,
        ?int $position,
        ?\DateTimeInterface $stylaUpdatedAt
    ) {
        $this->name = $name;
        $this->accountName = $accountName;
        $this->domain = $domain;
        $this->path = $path;
        $this->title = $title;
        $this->seoTitle = $seoTitle;
        $this->position = $position;
        $this->stylaUpdatedAt = $stylaUpdatedAt;

        $this->comparableRepresentationDataConverter = new ComparableRepresentationDataConverter();
    }

    public function toString(): string
    {
        return 'Is styla page match data';
    }

    /**
     * @param StylaPage|null $other
     *
     * @return bool
     */
    protected function doMatch($other): bool
    {
        Assert::assertNotNull($other, 'Styla page is null');
        Assert::assertEquals(
            $this->name,
            $other->getName(),
            'Styla synchronization "name" field value mismatch'
        );
        Assert::assertEquals(
            $this->accountName,
            $other->getAccountName(),
            'Styla synchronization "accountName" field value mismatch'
        );
        Assert::assertEquals(
            $this->domain,
            $other->getDomain(),
            'Styla synchronization "domain" field value mismatch'
        );
        Assert::assertEquals(
            $this->path,
            $other->getPath(),
            'Styla synchronization "path" field value mismatch'
        );
        Assert::assertEquals(
            $this->title,
            $other->getTitle(),
            'Styla synchronization "title" field value mismatch'
        );
        Assert::assertEquals(
            $this->seoTitle,
            $other->getSeoTitle(),
            'Styla synchronization "seoTitle" field value mismatch'
        );
        Assert::assertEquals(
            $this->position,
            $other->getPosition(),
            'Styla page "position" field value mismatch'
        );
        static::assertDateTime(
            $this->stylaUpdatedAt,
            $other->getStylaUpdatedAt(),
            'Styla page "stylaUpdatedAt" field value mismatch'
        );

        return true;
    }

    public function prepareExpectedValue(): ?array
    {
        return [
            self::NAME => $this->comparableRepresentationDataConverter->convertString($this->name),
            self::ACCOUNT_NAME => $this->comparableRepresentationDataConverter->convertString($this->accountName),
            self::DOMAIN => $this->comparableRepresentationDataConverter->convertString($this->domain),
            self::PATH => $this->comparableRepresentationDataConverter->convertString($this->path),
            self::TITLE => $this->comparableRepresentationDataConverter->convertString($this->title),
            self::SEO_TITLE => $this->comparableRepresentationDataConverter->convertString($this->seoTitle),
            self::POSITION => $this->comparableRepresentationDataConverter->convertInt($this->position),
            self::STYLA_UPDATED_AT => $this->comparableRepresentationDataConverter
                ->convertDateTime($this->stylaUpdatedAt),
        ];
    }

    /**
     * @param StylaPage $other
     *
     * @return array|null
     */
    public static function prepareActualValue($other): ?array
    {
        $dataConverted = new ComparableRepresentationDataConverter();
        return [
            self::NAME => $dataConverted->convertString($other->getName()),
            self::ACCOUNT_NAME => $dataConverted->convertString($other->getAccountName()),
            self::DOMAIN => $dataConverted->convertString($other->getDomain()),
            self::PATH => $dataConverted->convertString($other->getPath()),
            self::TITLE => $dataConverted->convertString($other->getTitle()),
            self::SEO_TITLE => $dataConverted->convertString($other->getSeoTitle()),
            self::POSITION => $dataConverted->convertInt($other->getPosition()),
            self::STYLA_UPDATED_AT => $dataConverted->convertDateTime($other->getStylaUpdatedAt()),
        ];
    }
}
