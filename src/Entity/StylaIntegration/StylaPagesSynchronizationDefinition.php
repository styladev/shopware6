<?php

namespace Styla\CmsIntegration\Entity\StylaIntegration;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class StylaPagesSynchronizationDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'styla_cms_pages_synchronization';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return StylaPagesSynchronizationsCollection::class;
    }

    public function getEntityClass(): string
    {
        return StylaPagesSynchronization::class;
    }

    protected function defineFields(): FieldCollection
    {
        $idField = new IdField('id', 'id');
        $idField->addFlags(new Required(), new PrimaryKey());

        $statusField = new StringField('status', 'status');
        $statusField->addFlags(new Required());

        $activeField = new BoolField('active', 'active');
        $activeField->addFlags(new Required());

        $startedAtField = new DateTimeField('started_at', 'startedAt');
        $finishedAtField = new DateTimeField(
            'finished_at',
            'finishedAt'
        );

        return new FieldCollection(
            [
                $idField,
                $statusField,
                $activeField,
                $startedAtField,
                $finishedAtField
            ]
        );
    }
}
