<?php

namespace Styla\CmsIntegration\Test\Constraint;

use PHPUnit\Framework\Assert;
use Styla\CmsIntegration\Entity\StylaIntegration\StylaPagesSynchronization;

class StylaSynchronizationMatchConstraint extends AbstractMultipleAssertConstraint
{
    private const CREATED_AT = 'createdAt';
    private const UPDATED_AT = 'updatedAt';
    private const STARTED_AT = 'startedAt';
    private const FINISHED_AT = 'finishedAt';
    private const ACTIVE = 'active';
    private const STATUS = 'status';

    private ComparableRepresentationDataConverter $comparableRepresentationDataConverter;

    private ?bool $isActive;
    private ?string $status;
    private ?\DateTimeInterface $startedAt;
    private ?\DateTimeInterface $finishedAt;
    private ?\DateTimeInterface $createdAt;
    private ?\DateTimeInterface $updatedAt;

    public function __construct(
        ?bool $isActive,
        ?string $status,
        ?\DateTimeInterface $startedAt,
        ?\DateTimeInterface $finishedAt,
        ?\DateTimeInterface $createdAt,
        ?\DateTimeInterface $updatedAt
    ) {
        $this->isActive = $isActive;
        $this->status = $status;
        $this->startedAt = $startedAt;
        $this->finishedAt = $finishedAt;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;

        $this->comparableRepresentationDataConverter = new ComparableRepresentationDataConverter();
    }

    public function toString(): string
    {
        return 'Is styla synchronization match data';
    }

    /**
     * @param StylaPagesSynchronization|null $other
     *
     * @return bool
     */
    protected function doMatch($other): bool
    {
        Assert::assertNotNull($other, 'Styla synchronization is null');
        Assert::assertEquals(
            $this->isActive,
            $other->getActive(),
            'Styla synchronization "active" field value mismatch'
        );
        Assert::assertEquals(
            $this->status,
            $other->getStatus(),
            'Styla synchronization "status" field value mismatch'
        );
        static::assertDateTime(
            $this->createdAt,
            $other->getCreatedAt(),
            'Styla synchronization "createdAt" field value mismatch'
        );
        static::assertDateTime(
            $this->updatedAt,
            $other->getUpdatedAt(),
            'Styla synchronization "updatedAt" field value mismatch'
        );
        static::assertDateTime(
            $this->startedAt,
            $other->getStartedAt(),
            'Styla synchronization "startedAt" field value mismatch'
        );
        static::assertDateTime(
            $this->finishedAt,
            $other->getFinishedAt(),
            'Styla synchronization "finishedAt" field value mismatch'
        );

        return true;
    }

    public function prepareExpectedValue(): ?array
    {
        return [
            self::CREATED_AT => $this->comparableRepresentationDataConverter->convertDateTime($this->createdAt),
            self::UPDATED_AT => $this->comparableRepresentationDataConverter->convertDateTime($this->updatedAt),
            self::STARTED_AT => $this->comparableRepresentationDataConverter->convertDateTime($this->startedAt),
            self::FINISHED_AT => $this->comparableRepresentationDataConverter->convertDateTime($this->finishedAt),
            self::ACTIVE => $this->comparableRepresentationDataConverter->convertBool($this->isActive),
            self::STATUS => $this->comparableRepresentationDataConverter->convertString($this->status),
        ];
    }

    /**
     * @param StylaPagesSynchronization|null $other
     *
     * @return array|null
     */
    public static function prepareActualValue($other): ?array
    {
        if (!$other) {
            return null;
        }

        $dataConverted = new ComparableRepresentationDataConverter();

        return [
            self::CREATED_AT => $dataConverted->convertDateTime($other->getCreatedAt()),
            self::UPDATED_AT => $dataConverted->convertDateTime($other->getUpdatedAt()),
            self::STARTED_AT => $dataConverted->convertDateTime($other->getStartedAt()),
            self::FINISHED_AT => $dataConverted->convertDateTime($other->getFinishedAt()),
            self::ACTIVE => $dataConverted->convertBool($other->getActive()),
            self::STATUS => $dataConverted->convertString($other->getStatus()),
        ];
    }
}
