<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategorieController extends AbstractController
{
    #[Route('/categorie', name: 'app_categorie')]
    public function index(): Response
    {
        return $this->render('categorie/index.html.twig', [
            'controller_name' => 'CategorieController',
        ]);
    }

    #[Route('/categorie/add', name: 'app_categorie_add')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Image handling has been removed

            $entityManager->persist($categorie);
            $entityManager->flush();

            $this->addFlash('success', 'Catégorie ajoutée avec succès!');

            return $this->redirectToRoute('app_categorie');
        }

        return $this->render('categorie/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/categorie/list', name: 'app_categorie_list')]
    public function list(Request $request, CategorieRepository $categorieRepository): Response
    {
        $searchTerm = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'nom'); // Par défaut, tri par nom

        if ($searchTerm) {
            $categories = $categorieRepository->findByName($searchTerm, $sort);
        } else {
            $categories = $categorieRepository->findAllSorted($sort);
        }

        return $this->render('categorie/list.html.twig', [
            'categories' => $categories,
            'searchTerm' => $searchTerm,
            'sort' => $sort,
        ]);
    }

    #[Route('/categorie/edit/{id}', name: 'app_categorie_edit')]
    public function edit(int $id, Request $request, CategorieRepository $categorieRepository, EntityManagerInterface $entityManager): Response
    {
        $categorie = $categorieRepository->find($id);
        if (!$categorie) {
            throw $this->createNotFoundException('No categorie found for id ' . $id);
        }

        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_categorie_list');
        }

        return $this->render('categorie/edit.html.twig', [
            'form' => $form->createView(),
            'categorie' => $categorie,
        ]);
    }

    #[Route('/categorie/delete/{id}', name: 'app_categorie_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, CategorieRepository $categorieRepository, EntityManagerInterface $entityManager): Response
    {
        $categorie = $categorieRepository->find($id);
        if (!$categorie) {
            throw $this->createNotFoundException('No categorie found for id ' . $id);
        }

        if ($this->isCsrfTokenValid('delete' . $categorie->getId(), $request->request->get('_token'))) {
            $entityManager->remove($categorie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_categorie_list');
    }

    #[Route('/categorie/affichage', name: 'categorie_affichage')]
    public function affichage(Request $request, CategorieRepository $categorieRepository): Response
    {
        $searchTerm = $request->query->get('search', '');

        if ($searchTerm) {
            $categories = $categorieRepository->findByName($searchTerm);
        } else {
            $categories = $categorieRepository->findAll();
        }

        return $this->render('categorie_f/liste.html.twig', [
            'categories' => $categories,
            'searchTerm' => $searchTerm,
        ]);
    }

    #[Route('/categorie/recherche', name: 'app_categorie_recherche')]
    public function recherche(Request $request, CategorieRepository $categorieRepository): Response
    {
        $searchTerm = $request->query->get('search', '');

        if ($searchTerm) {
            $categories = $categorieRepository->findByName($searchTerm);
        } else {
            $categories = $categorieRepository->findAll();
        }

        return $this->render('categorie_f/liste.html.twig', [
            'categories' => $categories,
            'searchTerm' => $searchTerm,
        ]);
    }
}
