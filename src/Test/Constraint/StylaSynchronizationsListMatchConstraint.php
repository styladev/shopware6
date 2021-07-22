<?php

namespace Styla\CmsIntegration\Test\Constraint;

class StylaSynchronizationsListMatchConstraint extends AbstractListMatchConstraint
{
    public function toString(): string
    {
        return 'Is styla synchronizations match data';
    }

    protected static function getListItemConstraintClassName(): string
    {
        return StylaSynchronizationMatchConstraint::class;
    }
}
