<?php

namespace App\Repository;
use App\Entity\Annonce;

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
}