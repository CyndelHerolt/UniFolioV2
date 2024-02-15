<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Constraints as Assert;

class PasswordRequirements extends Compound {

    protected function getConstraints(array $options): array
    {
        return [
            new Assert\NotBlank(),
            new Assert\Type('string'),
            new Assert\Length(['min' => 6]),
            //regex -> au moins 1 chiffre
            new Assert\Regex([
                'pattern' => '/\d+/i',
            ]),
            //regex -> au moins un caractère de la liste [#?!@$%^&*-]
            new Assert\Regex([
                'pattern' => '/[#?!@$%^&*-]+/i',
            ]),
        ];
    }
}