<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @Annotation
 */
final class ConfirmedPropertiesValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {

        /*    if (!array_diff(['description', 'price'], $value)) {*/
        $this->context->buildViolation($constraint->message."-".$value)->addViolation();
        /*}*/
    }
}