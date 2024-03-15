<?php
// src/Security/UserChecker.php
namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class UserChecker implements UserCheckerInterface
{
public function checkPreAuth(UserInterface $user): void
{
    if (!$user instanceof User) {
        throw new \Exception('The user is not an instance of User.');
    }

    if ($user->isIsVerified() === false) {
        // throw an exception if the user is not verified
        throw new CustomUserMessageAuthenticationException('Votre profil n\'est pas vérifié. Veuillez vérifier votre boîte mail pour activer votre compte ou faites une nouvelle demande de vérification.');
    }

    dd('checkPreAuth called');
}

    public function checkPostAuth(UserInterface $user): void
    {
        // you can also add checks after authentication here
    }
}