<?php


namespace App\Validator;



use Symfony\Component\Validator\Constraint;

/**
 * Constraint for the event closed validator.
 * It will check if event is finished (to leave comment for example)
 *
 * @Annotation
 */
class EventFinished extends Constraint
{

    public $message = 'Event is not finished';

}
