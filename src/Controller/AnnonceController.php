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
    
    #[Route(methods:'GET')]
    public function all() {
        return $this->json(
            $this->repo->findAll()
        );
    }

    #[Route('/{id}', methods:'DELETE')]
    public function remove(int $id) {
        $user = $this->getUser();
        $annonce = $this->repo->findById($id);
        if(!$annonce) {
            return $this->json('Annonce not found', 404);
        }
        if($annonce->getUser()->getId() != $user->getId()) {
            return $this->json('Unauthorized', 403);
        }
        $this->repo->remove($id);
        return $this->json(null, 204);
    }
}