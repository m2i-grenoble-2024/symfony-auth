<?php

namespace App\Entity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PasswordStrength;


class User implements UserInterface, PasswordAuthenticatedUserInterface {
    private ?int $id = null;
    #[Email]
    #[NotBlank]
    private ?string $email = null;
    #[NotBlank]
    // #[PasswordStrength] //annotation qui vérifie que le mot de passe est pas pourri, par défaut c'est sur une strength medium
    private ?string $password = null;
    private ?string $role = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getRoles(): array {
        return [$this->role];
    }
    public function eraseCredentials(): void{}
    public function getUserIdentifier(): string {
        return $this->email;
    }
}