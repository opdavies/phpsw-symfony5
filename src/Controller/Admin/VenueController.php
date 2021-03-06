<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Venue;
use App\Form\VenueDto;
use App\Form\VenueDtoType;
use App\Repository\VenueRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/venues")
 */
class VenueController extends AbstractController
{
    /**
     * @Route("", name="venueList")
     */
    public function home(VenueRepository $venueRepository): Response
    {
        return $this->render('admin/venueList.html.twig', [
            'venues' => $venueRepository->findAll(),
        ]);
    }

    /**
     * @Route("/create", name="venueCreate")
     */
    public function create(VenueRepository $venueRepository, Request $request): Response
    {
        $venueDto = VenueDto::newInstance();

        return $this->processVenueForm($venueRepository, $request, $venueDto, true);
    }

    /**
     * @Route("/{venue}", name="venueRead")
     */
    public function read(Venue $venue): Response
    {
        return $this->render('admin/venue.html.twig', [
            'venue' => $venue,
        ]);
    }

    /**
     * @Route("/{venue}/update", name="venueUpdate")
     */
    public function update(VenueRepository $venueRepository, Request $request, Venue $venue): Response
    {
        $venueDto = VenueDto::newInstanceFromVenue($venue);

        return $this->processVenueForm($venueRepository, $request, $venueDto, false);
    }

    /**
     * @Route("/{venue}/delete", name="venueDelete")
     */
    public function delete(VenueRepository $venueRepository, Request $request, Venue $venue): Response
    {
        if ($request->isMethod('POST')) {
            if ($venue->canDelete()) {
                $venueRepository->delete($venue);
                $this->addFlash(FlashLevels::SUCCESS, "Venue {$venue->getName()} has been deleted");
            } else {
                $this->addFlash(FlashLevels::DANGER, "Could not delete venue {$venue->getName()}");
            }

            return $this->redirectToRoute('venueList');
        }

        return $this->render('admin/venueDelete.html.twig', [
            'venue' => $venue,
        ]);
    }

    private function processVenueForm(
        VenueRepository $venueRepository,
        Request $request,
        VenueDto $venueDto,
        bool $isCreate
    ): Response {
        $form = $this->createForm(VenueDtoType::class, $venueDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $venue = $venueDto->asVenue();
            $venueRepository->persist($venue);
            $message = "Venue {$venue->getName()} ".($isCreate ? 'created' : 'updated');
            $this->addFlash(FlashLevels::SUCCESS, $message);

            return $this->redirectToRoute('venueList');
        }

        return $this->render('admin/venueForm.html.twig', [
            'isCreate' => $isCreate,
            'form' => $form->createView(),
        ]);
    }
}
