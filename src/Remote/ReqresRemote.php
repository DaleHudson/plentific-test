<?php

namespace Plentific\Remote;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Plentific\DTOs\UserDTO;
use Plentific\Exceptions\ApiException;
use Plentific\Validator\Validator;

class ReqresRemote implements RemoteInterface
{
    // TODO - look into PSR17 Request factory
    public function __construct(
        private ClientInterface $client
    ) {
    }

    public function getUserById(int $id): UserDTO
    {
        try {
            $response = $this->client->get("users/{$id}");

            $data = json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            throw new ApiException("Failed to retrieve user by Id: $id", $e->getCode(), $e);
        }

        return UserDTO::fromArray($data['data']);
    }

    public function getPaginatedUsers(int $page = 1): array
    {
        try {
            $response = $this->client->get("users", ['query' => ['page' => $page]]);

            $data = json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            throw new ApiException("Failed to retrieve paginated users on page: $page", $e->getCode(), $e);
        }

        return array_map(fn($user) => UserDTO::fromArray($user), $data['data']);
    }

    public function createUser(Validator $validator, array $data): int
    {
        $name = $data['name'] ?? '';
        $job = $data['job'] ?? '';

        $this->validateCreateUserData($validator, $name, $job);

        try {
            $response = $this->client->post("users", [
                'json' => [
                    'name' => $name,
                    'job' => $job
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            throw new ApiException("Failed to create user", $e->getCode(), $e);
        }

        return (int) $data['id'];
    }

    private function validateCreateUserData(Validator $validator, string $name, string $job): void
    {
        $validator->required('name', $name)
            ->minLength('name', $name)
            ->required('job', $job)
            ->minLength('job', $job)
            ->validate();
    }
}
