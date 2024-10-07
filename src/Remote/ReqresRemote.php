<?php

namespace Plentific\Remote;

use Plentific\DTOs\UserDTO;
use Plentific\Exceptions\ApiException;
use Plentific\Validator\Validator;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class ReqresRemote implements RemoteInterface
{
    public function __construct(
        private ClientInterface $client,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory
    ) {
    }

    public function getUserById(int $id): UserDTO
    {
        try {
            $request = $this->requestFactory->createRequest('GET', "users/{$id}");

            $response = $this->client->sendRequest($request);

            $data = json_decode($response->getBody()->getContents(), true);

            if ($data === null || !isset($data['data'])) {
                throw new ApiException("Failed to retrieve user by Id: $id");
            }

        } catch (ClientExceptionInterface $e) {
            throw new ApiException("Failed to retrieve user by Id: $id", $e->getCode(), $e);
        }

        return UserDTO::fromArray($data['data']);
    }

    public function getPaginatedUsers(int $page = 1): array
    {
        try {
            $uri = "users?page=$page";

            $request = $this->requestFactory->createRequest('GET', $uri);

            $response = $this->client->sendRequest($request);

            $data = json_decode($response->getBody()->getContents(), true);

            if ($data === null || !isset($data['data'])) {
                throw new ApiException("Failed to retrieve paginated users on page: $page");
            }
        } catch (ClientExceptionInterface $e) {
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
            $request = $this->requestFactory->createRequest('POST', "users")
                ->withHeader('Content-Type', 'application/json')
                ->withBody($this->createStreamFromArray([
                    'name' => $name,
                    'job' => $job
                ]));

            $response = $this->client->sendRequest($request);

            $data = json_decode($response->getBody()->getContents(), true);

            if ($data === null || !isset($data['id'])) {
                throw new ApiException("Failed to create user", 500);
            }
        } catch (ClientExceptionInterface $e) {
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

    private function createStreamFromArray(array $data): StreamInterface
    {
        $jsonData = json_encode($data);

        return $this->streamFactory->createStream($jsonData);
    }
}
