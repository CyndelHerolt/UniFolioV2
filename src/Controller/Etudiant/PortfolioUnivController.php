<?php

namespace App\Controller\Etudiant;

use App\Entity\Page;
use App\Entity\PortfolioUniv;
use App\Form\PageType;
use App\Form\PortfolioUnivType;
use App\Repository\PageRepository;
use App\Repository\PortfolioUnivRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/etudiant/portfolio/univ')]
class PortfolioUnivController extends AbstractController
{
    public function __construct(
        protected PortfolioUnivRepository $portfolioUnivRepository,
        protected PageRepository          $pageRepository,
    )
    {
    }

    #[Route('/', name: 'app_portfolio_univ')]
    public function index(): Response
    {
        return $this->render('portfolio_univ/index.html.twig', [
            'controller_name' => 'PortfolioUnivController',
        ]);
    }

    #[Route('/new', name: 'app_portfolio_univ_new')]
    public function create(Request $request): Response
    {
        $portfolio = new PortfolioUniv();

        $form = $this->createForm(PortfolioUnivType::class, $portfolio);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $portfolio->setLibelle($form->get('libelle')->getData());
            $portfolio->setDescription($form->get('description')->getData());
            $imageFile = $form['banniere']->getData();
            if ($imageFile) {
                $imageFileName = uniqid() . '.' . $imageFile->guessExtension();
                //Vérifier si le fichier est au bon format
                if (in_array($imageFile->guessExtension(), ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
                    $imageFile->move($_ENV['PATH_FILES'], $imageFileName);
                    $portfolio->setBanniere($_ENV['SRC_FILES'] . '/' . $imageFileName);
                } elseif (!in_array($imageFile->guessExtension(), ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
                    $this->addFlash('danger', 'L\'image doit être au format jpg, jpeg, png, gif, svg ou webp');
                }
            } else {
                $portfolio->setBanniere($_ENV['SRC_FILES'] . '/banniere.jpg');
            }
            $portfolio->setEtudiant($this->getUser()->getEtudiant());
            $portfolio->setAnnee($this->getUser()->getEtudiant()->getSemestre()->getAnnee());
            $portfolio->setVisibilite($form->get('visibilite')->getData());
            $portfolio->setDateCreation(new \DateTime('now'));
            $portfolio->setOptSearch($form->get('optSearch')->getData());

            $this->portfolioUnivRepository->save($portfolio, true);

            return $this->redirectToRoute('app_portfolio_univ_edit', ['id' => $portfolio->getId()]);
        }

        return $this->render('portfolio_univ/form.html.twig', [
            'portfolio' => $portfolio,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'app_portfolio_univ_edit')]
    public function edit(Request $request, PortfolioUniv $portfolio, ?string $step, ?bool $edit): Response
    {
        $step = $request->query->get('step', $step);

        if ($step === null) {
            $step = 'portfolio';
        }

        switch ($step) {
            case 'portfolio':
                $form = $this->createForm(PortfolioUnivType::class, $portfolio);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $portfolio->setLibelle($form->get('libelle')->getData());
                    $portfolio->setDescription($form->get('description')->getData());
                    $imageFile = $form['banniere']->getData();
                    if ($imageFile) {
                        $imageFileName = uniqid() . '.' . $imageFile->guessExtension();
                        //Vérifier si le fichier est au bon format
                        if (in_array($imageFile->guessExtension(), ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
                            $imageFile->move($_ENV['PATH_FILES'], $imageFileName);
                            $portfolio->setBanniere($_ENV['SRC_FILES'] . '/' . $imageFileName);
                        } elseif (!in_array($imageFile->guessExtension(), ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
                            $this->addFlash('danger', 'L\'image doit être au format jpg, jpeg, png, gif, svg ou webp');
                        }
                    } else {
                        $portfolio->setBanniere($_ENV['SRC_FILES'] . '/banniere.jpg');
                    }
                    $portfolio->setVisibilite($form->get('visibilite')->getData());
                    $portfolio->setOptSearch($form->get('optSearch')->getData());

                    $this->portfolioUnivRepository->save($portfolio, true);
                }
                break;

            case 'newPage':
                $page = new Page();
                $page->setPortfolio($portfolio);
                $allPages = $this->pageRepository->findBy(['portfolio' => $portfolio]);
                $page->setOrdre(count($allPages) + 1);
                $page->setLibelle('Nouvelle page');
                $this->pageRepository->save($page, true);

                $form = $this->createForm(PageType::class, $page);

                $step = 'page';
                break;

            case 'page':
                $page = $this->pageRepository->find($request->query->get('page'));
                $form = $this->createForm(PageType::class, $page);

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $page->setLibelle($form->get('libelle')->getData());
                    $page->setDescription($form->get('description')->getData());
                    $this->pageRepository->save($page, true);

                    $edit = false;
                }

                $edit = $request->query->get('edit', $edit);
                $step = 'page';
                break;
        }

        $pages = $portfolio->getPages();

        return $this->render('portfolio_univ/edit.html.twig', [
            'portfolio' => $portfolio,
            'pages' => $pages,
            'form' => $form->createView() ?? null,
            'step' => $step,
            'page' => $page ?? null,
            'edit' => $edit ?? true,
        ]);
    }
}
