<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Event;
use App\Entity\Reservation;
use App\Form\EventType;
use App\Form\ReservationType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Commentaire;
use App\Form\CommentaireType;
use App\Repository\CommentaireRepository;

class EventController extends AbstractController
{

    #[Route('/admin', name: 'display_admin')]
    public function indexAdmin(): Response
    {
        return $this->render('Admin/index.html.twig');
    }

    #[Route('/event/add', name: 'app_event_add')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('app_event_list');
        }

        return $this->render('event/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/event/list', name: 'app_event_list')]
    public function list(Request $request, EventRepository $eventRepository): Response
    {
        // Récupération du terme de recherche et du tri depuis la requête
        $searchTerm = $request->query->get('search', '');
        $sortOrder = $request->query->get('sort', 'asc');

        // Recherche d'événements correspondant au terme
        if ($searchTerm) {
            $events = $eventRepository->findByName($searchTerm, $sortOrder);
        } else {
            $events = $eventRepository->findAllOrderedByName($sortOrder);
        }

        return $this->render('event/list.html.twig', [
            'events' => $events,
            'searchTerm' => $searchTerm,
            'sortOrder' => $sortOrder,
        ]);
    }

    #[Route('/event/edit/{id}', name: 'app_event_edit')]
    public function edit(int $id, Request $request, EventRepository $eventRepository, EntityManagerInterface $entityManager): Response
    {
        $event = $eventRepository->find($id);
        if (!$event) {
            throw $this->createNotFoundException('No event found for id ' . $id);
        }

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_event_list');
        }

        return $this->render('event/edit.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    #[Route('/event/delete/{id}', name: 'app_event_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, EventRepository $eventRepository, EntityManagerInterface $entityManager): Response
    {
        $event = $eventRepository->find($id);
        if (!$event) {
            throw $this->createNotFoundException('No event found for id ' . $id);
        }

        if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_event_list');
    }

    #[Route('/event/search', name: 'app_event_search')]
    public function search(Request $request, EventRepository $eventRepository): Response
    {
        $searchTerm = $request->query->get('search', '');

        if ($searchTerm) {
            $events = $eventRepository->findByName($searchTerm);
        } else {
            $events = $eventRepository->findAll();
        }

        return $this->render('event/list.html.twig', [
            'events' => $events,
            'searchTerm' => $searchTerm,
        ]);
    }

    #[Route('/event/pdf', name: 'app_event_pdf')]
    public function generatePdf(EventRepository $eventRepository): Response
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);

        $events = $eventRepository->findAll();

        $html = $this->renderView('event/pdf.html.twig', [
            'events' => $events,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $pdfContent = $dompdf->output();
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="events.pdf"',
        ]);
    }

    #[Route('/event/stats', name: 'app_event_stats')]
    public function stats(EventRepository $eventRepository): Response
    {
        $stats = $eventRepository->getStatsByLocation();

        $locations = [];
        $places = [];

        foreach ($stats as $stat) {
            $locations[] = $stat['emplacement'];
            $places[] = $stat['number_of_places'];
        }

        return $this->render('event/stats.html.twig', [
            'locations' => json_encode($locations),
            'places' => json_encode($places),
        ]);
    }

    #[Route('/event/list/sorted', name: 'app_event_list_sorted')]
    public function listSorted(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAllOrderedByName();

        return $this->render('event/list_sorted.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/event/affichage', name: 'event_affichage')]
    public function affichage(Request $request, EventRepository $eventRepository): Response
    {
        $searchTerm = $request->query->get('search', '');

        if ($searchTerm) {
            $events = $eventRepository->findByName($searchTerm);
        } else {
            $events = $eventRepository->findAll();
        }

        return $this->render('event_f/liste.html.twig', [
            'events' => $events,
            'searchTerm' => $searchTerm,
        ]);
    }

    #[Route('/event/reserve/{id}', name: 'app_event_reserve')]
    public function reserve(Request $request, EntityManagerInterface $entityManager, EventRepository $eventRepository, int $id): Response
    {
        $event = $eventRepository->find($id);
        if (!$event) {
            throw $this->createNotFoundException('Aucun événement trouvé pour l\'ID ' . $id);
        }

        if ($event->getNombrePlaces() <= 0) {
            $this->addFlash('error', 'Le nombre de places est épuisé. Vous ne pouvez plus réserver cet événement.');
            return $this->redirectToRoute('event_affichage');
        }

        $form = $this->createForm(ReservationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reservation = $form->getData();
            $nombreplacesToReserve = $reservation->getNombreplaces();

            if ($nombreplacesToReserve > $event->getNombrePlaces()) {
                $this->addFlash('error', 'Le nombre de places à réserver est supérieur au nombre de places disponibles.');
            } else {
                $event->setNombrePlaces($event->getNombrePlaces() - $nombreplacesToReserve);
                $entityManager->flush();
                $this->addFlash('success', 'Le nombre de places a été réservé avec succès.');
            }

            return $this->redirectToRoute('event_affichage');
        }

        return $this->render('event_f/reserve.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    #[Route('/event/recherche', name: 'app_event_recherche')]
    public function recherche(Request $request, EventRepository $eventRepository): Response
    {
        $searchTerm = $request->query->get('search', '');

        if ($searchTerm) {
            $events = $eventRepository->findByName($searchTerm);
        } else {
            $events = $eventRepository->findAll();
        }

        return $this->render('event_f/liste.html.twig', [
            'events' => $events,
            'searchTerm' => $searchTerm,
        ]);
    }
    
    #[Route('/event/calendar', name: 'app_event_calendar')]
public function calendar(EventRepository $eventRepository): Response
{
    // Récupérer tous les événements
    $events = $eventRepository->findAll();

    return $this->render('event_f/calendar.html.twig', [
        'events' => $events,
    ]);
}

}
