<?php


namespace App\Validator;


use App\Entity\Event;
use App\Helpers\DateHelper;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validate event and check it finished
 */
class EventFinishedValidator extends ConstraintValidator
{

    /**
     * @param mixed $value
     * @param Constraint $constraint
     * @throws \Exception
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value) {
            return;
        }

        // If event end after today, it's not ended
        /** @var Event $value */
        if ($value->getEndAt() >= DateHelper::getToday()) {
            /* @var $constraint EventFinished */
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
