<?php


namespace App\Validator;


use Symfony\Component\Validator\Constraint;

/**
 * Constraint to avoid author to invite himself
 * @Annotation
 */
class NotEventAuthor extends Constraint
{
    public $message = 'Cannot invite author of the event';


    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

