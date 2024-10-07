<?php

namespace DTOs;

use Plentific\DTOs\UserDTO;
use PHPUnit\Framework\TestCase;

class UserDTOTest extends TestCase
{
    public function testFromArray()
    {
        $data = [
            'id' => 1,
            'email' => 'janedoe@example.com',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'avatar' => 'https://example.com/avatar.jpg'
        ];

        $userDTO = UserDTO::fromArray($data);

        $this->assertInstanceOf(UserDTO::class, $userDTO);
        $this->assertEquals(1, $userDTO->getId());
        $this->assertEquals('janedoe@example.com', $userDTO->getEmail());
        $this->assertEquals('Jane', $userDTO->getFirstName());
        $this->assertEquals('Doe', $userDTO->getLastName());
        $this->assertEquals('https://example.com/avatar.jpg', $userDTO->getAvatar());
    }

    public function testToArray()
    {
        $data = [
            'id' => 1,
            'email' => 'janedoe@example.com',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'avatar' => 'https://example.com/avatar.jpg'
        ];

        $userDTO = UserDTO::fromArray($data);
        $array = $userDTO->toArray();

        $this->assertIsArray($array);
        $this->assertEquals($data, $array);
    }
}
