<?php

namespace App\Controller;
use App\Entity\Annonce;
use App\Repository\AnnonceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/annonce')]
class AnnonceController extends AbstractController {
    public function __construct(private AnnonceRepository $repo) {}

    #[Route(methods:'POST')]
    public function add(#[MapRequestPayload] Annonce $annonce) {
        $annonce->setUser($this->getUser()); //On assigne le user connecté à l'annonce
        $this->repo->persist($annonce);
        return $this->json($annonce, 201);
    }
    
}