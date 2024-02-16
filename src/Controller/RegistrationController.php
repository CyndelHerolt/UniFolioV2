<?php

namespace App\Controller;

use App\Controller\SynchroIntranet\UserSynchro;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\BibliothequeRepository;
use App\Repository\DepartementRepository;
use App\Repository\EnseignantRepository;
use App\Repository\EtudiantRepository;
use App\Repository\GroupeRepository;
use App\Repository\SemestreRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        protected UserRepository $userRepository,
    )
    {
    }

    #[Route('/inscription', name: 'app_register')]
    public function register(
        Request                     $request,
        UserPasswordHasherInterface $userPasswordHasher,
        UserRepository              $userRepository,
        UserSynchro                 $userSynchro,
        HttpClientInterface         $client,
        MailerInterface             $mailer,
        VerifyEmailHelperInterface  $verifyEmailHelper,
    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $plainPassword = $request->get('registration_form')['plainPassword'];
            $pwdVerif = $request->request->get('pwd2');
//
            if ($plainPassword === $pwdVerif) {

                $login = $user->getUsername();

                if ($this->userRepository->findOneBy(['username' => $form->get('username')->getData()])) {
                    $this->addFlash('danger', 'Vous avez déjà un compte.');
                } else {
                    // encode the plain password
                    $user->setPassword(
                        $userPasswordHasher->hashPassword(
                            $user,
                            $form->get('plainPassword')->getData()
                        )
                    );

                    $checkEmailEtudiant = $userSynchro->CheckEmailEtudiant($login, $client, $mailer, $verifyEmailHelper);
                    $checkEmailEnseignant = $userSynchro->checkEmailEnseignant($login, $client, $mailer, $verifyEmailHelper);
                    if ($checkEmailEtudiant) {
                        $user->setRoles(['ROLE_ETUDIANT']);
                        $mailEtudiant = $userSynchro->getEmailEtudiant($login, $client);
                        $user->setEmail($mailEtudiant);
                        $userRepository->save($user, true);
                        $this->addFlash('success', 'Un mail de vérification vous a été envoyé. Veuillez cliquer sur le lien pour valider votre compte.');
                    } elseif ($checkEmailEnseignant) {
                        $user->setRoles(['ROLE_ENSEIGNANT']);
                        $mailEnseignant = $userSynchro->getEmailEnseignant($login, $client);
                        $user->setEmail($mailEnseignant);
                        $userRepository->save($user, true);
                        $this->addFlash('success', 'Un mail de vérification vous a été envoyé. Veuillez cliquer sur le lien pour valider votre compte.');
                    } else {
                        $this->addFlash('danger', 'Une erreur s\'est produite, veuillez vérifier que votre login URCA est correct. Si le problème persiste, veuillez contacter l\'administrateur du site.');
                        return $this->redirectToRoute('app_register', [], Response::HTTP_SEE_OTHER);
                    }
                }
                return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
            } else {
                $form->get('plainPassword')->addError(new FormError('Les deux mots de passe doivent être identiques.'));
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }
        }
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }


    #[Route('/verify', name: 'app_verify_email')]
    public function verifyUserEmail(
        Request $request,
        VerifyEmailHelperInterface $verifyEmailHelper,
        UserRepository $usersRepository,
        UserSynchro $userSynchro,
        HttpClientInterface $client,
        EtudiantRepository $etudiantRepository,
        BibliothequeRepository $bibliothequeRepository,
        EnseignantRepository $enseignantRepository,
        GroupeRepository $groupeRepository,
        DepartementRepository $departementRepository,
        SemestreRepository $semestreRepository,
    ): Response {
        $user = $usersRepository->findOneBy(['username' => $request->query->get('id')]);
        $login = $user->getUsername();
        try {
            $verifyEmailHelper->validateEmailConfirmation(
                $request->getUri(),
                $user->getUsername(),
                $user->getEmail(),
            );
        } catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('error', $e->getReason());
            return $this->redirectToRoute('app_register');
        }
        $etudiantSynchro = $userSynchro->synchroEtudiant($login, $user, $client, $etudiantRepository, $bibliothequeRepository, $groupeRepository, $semestreRepository);
        $enseignantSynchro = $userSynchro->synchroEnseignant($login, $user, $client, $enseignantRepository, $departementRepository);
        if ($etudiantSynchro) {
            $user->setIsVerified(true);
            $this->addFlash('success', 'Votre compte est vérifié, vos informations ont été mises à jour. Vous pouvez vous connecter.');
        } elseif ($enseignantSynchro) {
            $user->setIsVerified(true);
            $this->addFlash('success', 'Votre compte est vérifié, vos informations ont été mises à jour. Vous pouvez vous connecter.');
        }
        $usersRepository->save($user, true);
        return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/verify/resend', name: 'app_verify_resend_email')]
    public function resendVerifyEmail(
        Request $request,
        VerifyEmailHelperInterface $verifyEmailHelper,
        UserRepository $usersRepository,
        MailerInterface $mailer
    ) {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // récupérer les données du formulaire
            $mail = $request->request->get('mail-univ');

            $user = $usersRepository->findOneBy(['email' => $mail]);

            if ($user === null) {
                $this->addFlash('danger', 'Aucun compte n\'est associé à cette adresse mail. Si le problème persiste, veuillez contacter l\'administrateur du site.');
                return $this->redirectToRoute('app_verify_resend_email');
            }

            if ($user->getEtudiant()) {
                $userInfos = $user->getEtudiant();
            } elseif ($user->getEnseignant()) {
                $userInfos = $user->getEnseignant();
            } else {
                $userInfos = null;
            }

            if ($user && $user->isIsVerified() === false) {
                $signatureComponents = $verifyEmailHelper->generateSignature(
                    'app_verify_email',
                    $user->getUsername(),
                    $user->getEmail(),
                    ['id' => $user->getUsername()]
                );

                $email = (new TemplatedEmail())
                    ->from(new Address('portfolio.iut-troyes@univ-reims.fr', 'UniFolio Mail Bot'))
                    ->to($user->getEmail())
                    ->subject('UniFolio - Vérification de compte - Action Requise')
                    ->htmlTemplate('email.html.twig')
                    ->context([
                        'confirm_link' => $signatureComponents->getSignedUrl(),
                        'user' => $userInfos,
                        'email_subject' => 'Vérification de votre compte',
                        'email_message' => '<p>Pour accéder à votre compte, il est impératif de vérifier votre adresse mail. Cela garantit la sécurité de vos données personnelles.</p>
                                    <p>Pour ce faire, cliquez sur le bouton ci-dessous.</p>
                                    <p>Si vous n\'êtes pas à l\'origine de cette demande, que vous rencontrez des difficultés ou que vous avez des questions, merci de ne pas cliquer sur le bouton et de nous contacter à portfolio.iut-troyes@univ-reims.fr.</p>
                                    <p>Cordialement,</p>',
                        'email_button' => 'confirm_email'
                    ]);
                $mailer->send($email);

                $this->addFlash('success', 'Un nouveau mail de vérification vous a été envoyé. Veuillez cliquer sur le lien pour valider votre compte.');
            } elseif (!$user) {
                $this->addFlash('danger', 'Aucun compte n\'est associé à cette adresse mail.');
            } elseif ($user->isIsVerified() === true) {
                $this->addFlash('danger', 'Votre compte est déjà vérifié. Si vous ne parvenez toujours pas à vous connecter, veuillez contacter l\'administrateur du site.');
            }
        }

        return $this->render('registration/resend_verify_email.html.twig', [
        ]);
    }
}
