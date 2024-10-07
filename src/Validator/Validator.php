<?php

namespace Plentific\Validator;

use Plentific\Exceptions\ValidationException;

class Validator
{
    public function __construct(
        private array $errors = []
    ) {
    }

    public function required(string $field, mixed $value): self
    {
        if (empty($value)) {
            $this->errors[$field][] = 'The ' . $field . ' field is required.';
        }

        return $this;
    }

    public function minLength(string $field, mixed $value, int $minLength = 3): self
    {
        if (strlen($value) < $minLength) {
            $this->errors[$field][] = 'The ' . $field . ' field must be at least ' . $minLength . ' characters.';
        }

        return $this;
    }

    public function validate(): void
    {
        if ($this->getErrors()) {
            throw new ValidationException("Validation failed when creating user");
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
