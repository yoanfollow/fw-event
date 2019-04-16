<?php
// api/src/Validator/Constraints/MinimalProperties.php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ConfirmedProperties extends Constraint
{
    public $message = 'Invitation expirée.';
}