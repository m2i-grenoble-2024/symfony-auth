<?php


namespace App\Controller;
use App\Entity\User;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    //Exemple de ce que va faire la library JWT lorsqu'on se connecte et qu'on génère un token
    // Attention : Ne pas forcément utiliser car la library le fait déjà en plus paramètrable
    // #[Route('/api/login', methods: 'POST')]
    // public function login(#[MapRequestPayload] User $user, UserPasswordHasherInterface $hasher, JWTTokenManagerInterface $jwtManager) {
    //     $stored = $this->repo->findByEmail($user->getEmail());
    //     //On vérifie si on a bien un user qui correspond et ensuite si le mot de passe correspond au hash
    //     if(!$stored || !$hasher->isPasswordValid($stored, $user->getPassword())) {
    //         return $this->json('Login failed', 401);
    //     }
    //     //On génère un token à partir de notre user stocké (pasque c'est lui qui a toutes ses valeurs)
    //     $token = $jwtManager->create($stored);
    //     //On renvoie le token dans une réponse JSON
    //     return $this->json($token);
    // }

    //Exemple de ce que va faire la library JWT avant chacune des requêtes avec un Token
    // Attention : Ne pas utiliser dans la vraie, pasque c'est pas très bien comme code en vrai
    // #[Route('/api/protected', methods:'GET')]
    // public function protectedRoute(Request $request, JWTTokenManagerInterface $jwtManager) {
    //     $auth = $request->headers->get('Authorization');
    //     $token = substr($auth, 7);
    //     $payload = $jwtManager->parse($token);
    //     if($payload['exp'] < time()) {
    //         return $this->json('Expired token', 401);
    //     }
    //     $connectedUser = $this->repo->findByEmail($payload['username']);
    //     return $this->json('Welcome '.$connectedUser->getEmail());
    // }
}