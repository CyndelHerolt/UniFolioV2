<?php

// Charger l'autoloader de Composer
require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

// Configurer le transport SMTP avec vos identifiants Mailtrap
$transport = Transport::fromDsn('smtp://b045cd8e2ce44a:fd2579a26e6368@sandbox.smtp.mailtrap.io:2525');

// Créer l'instance du Mailer
$mailer = new Mailer($transport);

// Créer un e-mail de test
$email = (new Email())
    ->from('votre@email.com') // Remplacez par votre adresse e-mail
    ->to('destinataire@example.com') // Remplacez par l'adresse e-mail du destinataire
    ->subject('Test de connexion SMTP depuis Symfony')
    ->text('Ceci est un test de connexion SMTP.');

// Envoyer l'e-mail
try {
    $mailer->send($email);
    echo 'L\'e-mail de test a été envoyé avec succès.';
} catch (Throwable $e) {
    echo 'Une erreur s\'est produite lors de l\'envoi de l\'e-mail : ' . $e->getMessage();
}
