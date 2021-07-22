<?php

namespace Styla\CmsIntegration\Entity\StylaPage;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\AllowEmptyString;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class StylaPageDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'styla_cms_page';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return StylaPageCollection::class;
    }

    public function getEntityClass(): string
    {
        return StylaPage::class;
    }

    protected function defineFields(): FieldCollection
    {
        $idField = new IdField('id', 'id');
        $idField->addFlags(new Required(), new PrimaryKey());

        $nameField = new StringField('name', 'name');
        $nameField->addFlags(new Required(), new AllowEmptyString());

        $accountNameField = new StringField('account_name', 'accountName');
        $accountNameField->addFlags(new Required());

        $pathField = new StringField('path', 'path');
        $pathField->addFlags(new Required(), new AllowEmptyString());

        $domainField = new StringField('domain', 'domain');

        $titleField = new StringField('title', 'title');

        $seoTitleField = new StringField('seo_title', 'seoTitle');

        $positionField = new IntField('position', 'position');

        $stylaUpdatedAtField = new DateTimeField(
            'styla_updated_at',
            'stylaUpdatedAt'
        );

        return new FieldCollection(
            [
                $idField,
                $nameField,
                $accountNameField,
                $pathField,
                $domainField,
                $titleField,
                $seoTitleField,
                $positionField,
                $stylaUpdatedAtField
            ]
        );
    }
}
