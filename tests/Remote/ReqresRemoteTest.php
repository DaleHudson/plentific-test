<?php

namespace Remote;

use Faker\Factory;
use Faker\Generator;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use Plentific\DTOs\UserDTO;
use Plentific\Exceptions\ApiException;
use Plentific\Exceptions\ValidationException;
use Plentific\Remote\ReqresRemote;
use PHPUnit\Framework\TestCase;
use Plentific\Validator\Validator;

class ReqresRemoteTest extends TestCase
{
    private const BASE_URL = 'https://reqres.in/api/';

    protected Generator $faker;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create("en_GB");
    }

    public function testRemoteCanBeInstantiated()
    {
        $remote = new ReqresRemote(new Client());

        $this->assertInstanceOf(ReqresRemote::class, $remote);
        $this->assertObjectHasProperty('client', $remote);
    }

    public function testCanGetUserById()
    {
        $responseBody = $this->createResponseData([
            'id' => 2,
            'email' => $this->faker->email(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'avatar' => $this->faker->imageUrl(),
        ]);

        $client = $this->createMockClient(new Response(200, [], $responseBody));

        $subject = new ReqresRemote($client);

        $user = $subject->getUserById(2);

        $this->assertInstanceOf(UserDTO::class, $user);
    }

    public function testGetUserByIdNotFound()
    {
        $client = $this->createMockClient(new Response(404, [], 'User not found'));

        $subject = new ReqresRemote($client);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Failed to retrieve user by Id: 99999');

        $subject->getUserById(99999);
    }


    public function testCanGetPaginatedUsers()
    {
        $responseData = [];

        for ($i = 1; $i < 7; $i++) {
            $responseData[] = [
                'id' => $i,
                'email' => $this->faker->email(),
                'first_name' => $this->faker->firstName(),
                'last_name' => $this->faker->lastName(),
                'avatar' => $this->faker->imageUrl(),
            ];
        }

        $responseBody = $this->createResponseData($responseData);

        $client = $this->createMockClient(new Response(200, [], $responseBody));

        $subject = new ReqresRemote($client);

        $users = $subject->getPaginatedUsers();

        $this->assertCount(6, $users);

        foreach ($users as $user) {
            $this->assertInstanceOf(UserDTO::class, $user);
            $this->assertIsInt($user->getId());
            $this->assertIsString($user->getEmail());
            $this->assertIsString($user->getFirstName());
            $this->assertIsString($user->getLastName());
            $this->assertIsString($user->getAvatar());
        }
    }

    public function testGetPaginatedUsersEmptyResponse()
    {
        $responseBody = $this->createResponseData([]);

        $client = $this->createMockClient(new Response(200, [], $responseBody));

        $subject = new ReqresRemote($client);

        $results = $subject->getPaginatedUsers();

        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    public function testCreateUserSuccess()
    {
        $name = $this->faker->name();
        $job = $this->faker->jobTitle();
        $id = $this->faker->randomNumber(3);

        $responseBody = json_encode([
            'id' => $id,
            'name' => $name,
            'job' => $job,
        ]);

        $client = $this->createMockClient(new Response(201, [], $responseBody));

        $validator = $this->createMockValidator();

        $subject = new ReqresRemote($client);
        $result = $subject->createUser($validator, [
            'name' => $name,
            'job' => $job,
        ]);

        $this->assertIsInt($result);
        $this->assertEquals($id, $result);
    }

    public function testCannotCreateUserWithMissingData()
    {
        $validator = $this->createMock(Validator::class);
        $validator->expects($this->exactly(2))
            ->method('required')
            ->willReturnSelf();
        $validator->expects($this->exactly(2))
            ->method('minLength')
            ->willReturnSelf();
        $validator->expects($this->once())
            ->method('validate')
            ->willThrowException(new ValidationException('Validation failed when creating user'));

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Validation failed when creating user');

        $client = new Client([
            'base_uri' => self::BASE_URL,
        ]);

        $subject = new ReqresRemote($client);
        $subject->createUser($validator, [
            'name' => '',
            'job' => 'Developer',
        ]);
    }

    public function testInternalServerErrorIsHandled()
    {
        $client = $this->createMockClient(new Response(500, [], 'Internal Server Error'));

        $validator = $this->createMockValidator();

        $subject = new ReqresRemote($client);

        $this->expectExceptionMessage('Failed to create user');
        $this->expectExceptionCode(500);

        $subject->createUser($validator, [
            'name' => $this->faker->name(),
            'job' => $this->faker->jobTitle(),
        ]);
    }

    public function testBadRequestErrorIsHandled()
    {
        $client = $this->createMockClient(new Response(400, [], 'Bad Request'));

        $subject = new ReqresRemote($client);

        $this->expectException(ApiException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('Failed to retrieve paginated users on page: 1');

        $subject->getPaginatedUsers();
    }

    private function createMockClient(Response $response): Client
    {
        $mock = new MockHandler([$response]);
        $handlerStack = HandlerStack::create($mock);

        return new Client([
            'handler' => $handlerStack,
            'base_uri' => self::BASE_URL,
        ]);
    }

    private function createResponseData(array $data): string
    {
        return json_encode(['data' => $data]);
    }

    // Generate a method for the validator mock
    private function createMockValidator(): MockObject
    {
        $validator = $this->createMock(Validator::class);
        $validator->expects($this->exactly(2))
            ->method('required')
            ->willReturnSelf();
        $validator->expects($this->exactly(2))
            ->method('minLength')
            ->willReturnSelf();
        $validator->expects($this->once())
            ->method('validate');

        return $validator;
    }
}
