<?php


namespace App\Helpers;

use Symfony\Component\Form\FormInterface;

class FormHelper
{

    /**
     * @param FormInterface $form
     * @return array
     */
    public static function getErrorMessages(FormInterface $form)
    {
        $errors = [];

        foreach ($form->getErrors() as $key => $error) {
            if ($form->isRoot()) {
                $errors['#'][] = $error->getMessage();
            } else {
                $errors[] = $error->getMessage();
            }
        }

        /** @var FormInterface $child */
        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = self::getErrorMessages($child);
            }
        }

        return $errors;
    }
}
