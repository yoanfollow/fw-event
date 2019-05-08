<?php


namespace App\Validator;


use App\Entity\Invitation;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NotEventAuthorValidator extends ConstraintValidator
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

        if (!$value instanceof Invitation) {
            throw new \Exception('This constraint must be used for entity App/Entity/Invitation');
        }

        if (empty($value->getEvent())) {
            throw new \Exception('Event is empty');
        }

        if ($value->getRecipient()->getId() === $value->getEvent()->getOrganizer()->getId()) {
            /* @var $constraint NotEventAuthor */
            $this->context
                ->buildViolation($constraint->message)
                ->atPath('recipient')
                ->addViolation()
            ;
        }
    }
}

