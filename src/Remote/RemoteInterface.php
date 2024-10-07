<?php

namespace Plentific\Remote;

use Plentific\Validator\Validator;

interface RemoteInterface
{
    public function getUserById(int $id);

    public function getPaginatedUsers(int $page = 1): array;

    public function createUser(Validator $validator, array $data);
}
