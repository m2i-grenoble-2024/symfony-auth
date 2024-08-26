<?php


namespace App\Service;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Classe qui va permettre à Symfony de savoir comment récupérer un User dans la base
 * de données. C'est nécessaire car nous n'utilisons pas Doctrine dans ce projet et donc
 * Symfony ne peut pas deviner qu'il faut utiliser le Repo et le findByEmail.
 */
class UserProvider implements UserProviderInterface {
    public function __construct(private UserRepository $repo){}
    public function loadUserByIdentifier(string $identifier): UserInterface {
        //On fait l'appel au repository
        $stored = $this->repo->findByEmail($identifier);
        //On vérifie si on a rien trouvé
        if(!$stored) {
            //Si c'est le cas, on throw une erreur spécifique pour dire qu'on a pas trouvé de user correspondant
            throw new UserNotFoundException('User Not found');
        }
        //Sinon, on renvoie le User
        return $stored;
    }
    public function refreshUser(UserInterface $user): UserInterface {
        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }
    public function supportsClass(string $class): bool {
        return $class == User::class;
    }
}