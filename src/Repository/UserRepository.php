<?php

namespace App\Repository;
use App\Entity\User;

class UserRepository {

    public function persist(User $user) {
        $connection = Database::connect();
        $query = $connection->prepare('INSERT INTO user (email,password,role) VALUES (:email,:password,:role)');
        $query->bindValue(':email', $user->getEmail());
        $query->bindValue(':password', $user->getPassword());
        $query->bindValue(':role', $user->getRole());
        $query->execute();

        $user->setId($connection->lastInsertId());
    }

    public function findByEmail(string $email): ?User {
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
}