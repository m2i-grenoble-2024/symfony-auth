<?php

namespace App\Repository;
use App\Entity\Annonce;
use App\Entity\User;

class AnnonceRepository {

    public function persist(Annonce $annonce) {
        $connection = Database::connect();
        $query = $connection->prepare('INSERT INTO annonce (title,description,price,user_id) VALUES (:title,:description,:price,:userId)');
        $query->bindValue(':title', $annonce->getTitle());
        $query->bindValue(':description', $annonce->getDescription());
        $query->bindValue(':price', $annonce->getPrice());
        $query->bindValue(':userId', $annonce->getUser()->getId()); //On va chercher l'id dans le user

        $query->execute();

        $annonce->setId($connection->lastInsertId());
    }
    /**
     * 
     * 
     * @return array<Annonce>
     */
    public function findAll():array {
        $connection = Database::connect();
        $query = $connection->prepare('SELECT annonce.*, user.email FROM annonce INNER JOIN user ON user.id=annonce.user_id');
        $query->execute();
        $list= [];
        foreach($query->fetchAll() as $line) {
            $user = new User();
            $user->setId($line['user_id']);
            $user->setEmail($line['email']);
            $annonce = new Annonce();
            $annonce->setId($line['id']);
            $annonce->setTitle($line['title']);
            $annonce->setDescription($line['description']);
            $annonce->setPrice($line['price']);
            $annonce->setUser($user);
            $list[] = $annonce;
        }
        return $list;
    }
}