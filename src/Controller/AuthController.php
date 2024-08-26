<?php


namespace App\Controller;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class AuthController extends AbstractController {

    public function __construct(private UserRepository $repo){}

    #[Route('/api/user', methods:'POST')]
    public function register(
        #[MapRequestPayload] User $user,
        UserPasswordHasherInterface $hasher){

            //On vérifie s'il y a déjà un user avec ce meme email
            if($this->repo->findByEmail($user->getEmail()) != null){
                //Si oui, on arrête là, et on envoie une erreur
                return $this->json('User already exists', 400);
            }
            //On hash le mot de passe en utilisant l'algorithme défini dans le security.yaml
            $hashedPassword = $hasher->hashPassword($user, $user->getPassword());
            //On assigne au user le mot de passe hashé pour le stocker
            $user->setPassword($hashedPassword);
            //On assigne un role par défaut à notre user
            $user->setRole('ROLE_USER');
            //On fait persister le user dans la bdd
            $this->repo->persist($user);
            //On renvoie une réponse de succés
            return $this->json($user, 201);

    }
    
}