<?php

namespace Plentific\DTOs;

use JsonSerializable;

readonly class UserDTO implements JsonSerializable
{
    public function __construct(
        private int $id,
        private string $email,
        private string $firstName,
        private string $lastName,
        private string $avatar
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'avatar' => $this->avatar,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getAvatar(): string
    {
        return $this->avatar;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['email'],
            $data['first_name'],
            $data['last_name'],
            $data['avatar']
        );
    }
}
