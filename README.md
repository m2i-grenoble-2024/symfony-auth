# Autentification JWT avec Symfony
Projet dans lequel on utilise Symfony Security et le Lexik JWT Bundle pour créer une inscription avec hashage de mot de passe et une authentification avec JWT

## How To Use
1. Cloner le projet
2. Créer la base de données (`dam_grenoble_auth` par défaut) et importer le [database.sql](database.sql) dedans
3. Faire un `composer install`
4. Générer les clés privée/publique avec `php bin/console lexik:jwt:generate-keypair` (si ça ne marche pas sur windows, [installer OpenSSL](https://slproweb.com/products/Win32OpenSSL.html))
5. Lancer le serveur avec `symfony server:start`

## Pour se logger
1. Envoyer une requête POST vers http://localhost:8000/api/login avec comme body :
```json
{
    "username":"test@test.com",
    "password": "1234"
}
```
2. Copier la valeur du token renvoyée
3. Pour tester le token, faire une requête vers http://localhost:8000/api/secret en GET en mettant dans l'onglet Auth -> Bearer le token copié précédemment




## L'Authentification

### Création de l'entité
Dépendance nécessaire (si pas déjà installée) : `composer req security` 

On commence par créer une entité qui servira de user. Elle peut s'appeler comme on le souhaite, avoir n'importe quelles propriétés, pour être utilisée comme user il faut simplement qu'elle implémente les interface `UserInterface` et `PasswordAuthenticatedUserInterface`

```php

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    //...

    /**
     * Méthode indiquant à Symfony Security comment récupérer le password (au cas où la propriété ne
     * s'appelerait pas password dans l'entité)
     */
    public function getPassword() {
        return $this->password;
    }
    /**
     * Méthode indiquant les rôles du User, si on a qu'un seul rôle, on peut faire en sorte de return
     * directement ['ROLE_USER'] par exemple, mais si on a plusieurs rôles mais un seul simultané, on peut
     * faire comme ici.
     */
    public function getRoles(): array {
        return [$this->role];
	}
	/**
     * Méthode qui sert à remettre à zéro les données sensibles dans l'entité pour ne pas les persistées
     * (par exemple si on avait un champ pour le mot de passe en clair différent du champ password)
     */
	public function eraseCredentials() {
	}
	/**
     * Méthode indiquant à Symfony Security l'identifiant du user, donc soit le mail, soit le username
     * soit le téléphone, soit le numéro de sécu etc.
     */
	public function getUserIdentifier(): string {
        return $this->email;
	}
```

On crée un UserRepository avec un `findByEmail(string $email):?User` (ou n'importe quoi d'autre que email, selon l'identifiant choisit)

```php
    public function findByEmail(string $email):?User {
        
        $connection = Database::connect();
        $query = $connection->prepare('SELECT * FROM user WHERE email=:email');
        $query->bindValue(':email', $email);
        $query->execute();

        if($line = $query->fetch()) {
            $user = new User();
            $user->setId($line['id']);
            $user->setEmail($line['email']);
            $user->setPassword($line['password']);
            $user->setRole($line['role']);
            return $user;
        }
        return null;
   }
```

On crée une classe provider qui indiquera à Symfony comment récupérer un User dans la base de données lors d'une connexion. Cette classe doit implémenter l'interface UserProviderInterface

```php
class UserProvider implements UserProviderInterface {

    public function __construct(private UserRepository $repo){}
    /**
     * Méthode qui sera utilisée par Symfony lors d'une tentative de connexion pour
     * récupérer le User s'il existe. On y utilise notre repository pour chercher
     * le user dans la base de données
     */
    public function loadUserByIdentifier(string $identifier):UserInterface {
        $user = $this->repo->findByEmail($identifier);
        if($user == null) {
            throw new UserNotFoundException();
        }
        return $user;
    }
    /**
     * Méthode utilisée pour mettre à jour un user stockée en session, dans le cas
     * d'une connexion en JWT, cette méthode n'est pas très importante, on lui fait
     * juste relancer la méthode load
     */
    public function refreshUser(UserInterface $userInterface):UserInterface {
        return $this->loadUserByIdentifier($userInterface->getUserIdentifier());
    }

    /**
     * Méthode qui indique pour classe classe ce Provider devra s'appliquer. Ici ça
     * sera pour notre classe User. Cette méthode est surtout importante pour le cas
     * où on a plusieurs classes UserInterface différentes (ce qui arrive rarement)
     */ 
    public function supportsClass(string $class):bool {
        return $class == User::class;
    }
}
```


Dans le fichier `config/packages/security.yaml`, on rajoute un provider indiquant à symfony comment récupérer le user

```yaml
security:
    # ...
    providers:
        user_provider:
            id: App\Security\UserProvider
```

### Route d'inscription
On crée une route d'inscription dans un contrôleur dans laquelle il faudra : 
* Récupérer les données d'inscription obligatoire (au minimum identifiant et mot de passe) et les valider
* Vérifier qu'un user avec l'identifiant donné n'existe pas déjà
* Hasher le mot de passe et l'assigner à l'instance de l'entité
* Assigner les valeurs par défaut nécessaire (par exemple le rôle par défaut, un panier vide si besoin, la date d'inscription, etc.)
* Faire persister le user
Exemple de ces différentes étapes [ici dans la méthode register()](src/Controller/AuthController.php)

### Authentification par JWT
Dépendance nécessaire (si pas déjà installée) : `composer req jwt` 

Dans le cadre d'une application client-serveur, une des manières d'authentification les plus utilisées est celle par JWT. Le flow d'authentification est le suivant :
1. On fait une requête avec nos credentials (email/password par exemple) vers une route de login. Le serveur vérifie si un user avec cet identifiant existe, si oui on récupère le password hashé stocké en base de donnée, on utilise ses information pour hasher le mot de passe de login de la même manière, si les deux hash correspondent, c'est correct, on crée un JWT renvoyé au client
2. Le client stock le JWT quelque part (en localStorage par exemple). Toute nouvelle requête contiendra le JWT dans ses Authorization Headers
3. Lorsque le serveur reçoit une requête avec un JWT, il vérifie la validité de celui ci (est-ce qu'il a été altéré, sa signature est-elle ok, est-il expiré)
4. Si le token est valide, Symfony récupère son contenu et s'en sert pour récupérer en base de données l'entité user correspondante à ce token
5. Symfony vérifie si le user récupéré a accès à la ressource demandée, vis à vis de ce qui a été défini par exemple dans les access_control du security.yaml

#### Configuration de l'extension JWT
Dans le fichier de configuration `config/packages/security.yaml`, on rajoute :
```yaml
security:
    #...    
    firewalls:
        #...
        login:
            pattern: ^/api/login
            stateless: true
            json_login:
                check_path: /api/login
                success_handler: lexik_jwt_authentication.handler.authentication_success
                failure_handler: lexik_jwt_authentication.handler.authentication_failure
                username_path: email
        api:
            pattern:   ^/api
            stateless: true
            jwt: ~

```

La partie login contien dans le check_path la route qui permettra de s'authentifier, on peut la modifier, il faut juste que le check_path corresponde au pattern.

Dans la partie login, on indique quelles url de notre serveur utiliseront l'authentification par JWT, ici ça sera toutes les routes commençant par /api (c'est techniquement modifiable)


Dans le fichier de configuration `config/routes.yaml`, on rajoute :
```yaml
api_login_check:
    path: /api/login
```
Où le path devra correspondre au check_path indiqué dans le security.yaml

Ensuite on génère une paire de clés privée/publique avec `bin/console lexik:jwt:generate-keypair`

Dans le fichier `config/packages/lexik_jwt_authentication.yaml` on peut rajouter un `token_ttl: 100000000000` par exemple pour créer un token avec une date d'expiration très lointaine pour les tests (à retirer en prod)

#### Utilisation de l'authentification JWT avec ThunderClient
1. On fait une requête en POST vers `http://localhost:8000/api/login` en indiquant dans le body 
   ```json
    {
        "email":"identifiant@mail.com",
        "password":"leMotDePasse"
    }
    ```
2. On copie le token renvoyé
3. Pour chaque nouvelle requête, on rajoute dans l'onglet Auth > Bearer le token copié


### Sécurisation des routes
Pour sécuriser les routes, on peut rajouter dans le `config/packages/security.yaml`, dans la partie `access_control` les routes accessibles ou non

```yaml
security:
    enable_authenticator_manager: true
    #...
    access_control:
        - { path: ^/api/admin, roles: ROLE_ADMIN }
        - { path: /api/truc, roles: PUBLIC_ACCESS, methods:[GET] }
        - { path: /api/truc, roles: ROLE_USER }
```
Ici, toutes les routes commençant par /api/admin ne seront accessibles que par les user avec un rôle ROLE_ADMIN, la route /api/truc en GET sera accessible par n'importe qui, toutes les autres méthodes de la route /api/truc ne seront accessible que par les user avec le rôle ROLE_USER
