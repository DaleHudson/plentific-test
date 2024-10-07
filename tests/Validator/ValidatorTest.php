<?php

namespace Validator;

use Plentific\Exceptions\ValidationException;
use Plentific\Validator\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function testRequiredField()
    {
        $validator = new Validator();
        $validator->required('name', '');

        $errors = $validator->getErrors();
        $this->assertArrayHasKey('name', $errors);
        $this->assertContains('The name field is required.', $errors['name']);
    }

    public function testMinLengthField()
    {
        $validator = new Validator();
        $validator->minLength('name', 'Jo', 3);

        $errors = $validator->getErrors();
        $this->assertArrayHasKey('name', $errors);
        $this->assertContains('The name field must be at least 3 characters.', $errors['name']);
    }

    public function testValidateThrowsException()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Validation failed when creating user');

        $validator = new Validator();
        $validator->required('name', '');
        $validator->validate();
    }
}
