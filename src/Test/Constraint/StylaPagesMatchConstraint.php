<?php

namespace Styla\CmsIntegration\Test\Constraint;

class StylaPagesMatchConstraint extends AbstractListMatchConstraint
{
    public function toString(): string
    {
        return 'Is styla pages match data';
    }

    protected static function getListItemConstraintClassName(): string
    {
        return StylaPageMatchConstraint::class;
    }
}
