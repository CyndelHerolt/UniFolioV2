<?php

namespace App\Controller\Etudiant;

use App\Entity\Cv;
use App\Form\CvType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/etudiant')]
class CvController extends AbstractController
{
    #[Route('/cv', name: 'app_cv')]
    public function index(): Response
    {
        return $this->render('cv/index.html.twig', [
            'controller_name' => 'CvController',
        ]);
    }

    #[Route('/cv/new', name: 'app_cv_new')]
    public function new(Request $request): Response
    {
        $cv = new Cv();

        $form = $this->createForm(CvType::class, $cv);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $cv = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($cv);
            $entityManager->flush();

            return $this->redirectToRoute('app_cv');
        }

        return $this->render('cv/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
