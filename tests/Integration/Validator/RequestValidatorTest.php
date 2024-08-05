<?php

declare(strict_types=1);

namespace Tests\Webgriffe\SyliusElasticsearchPlugin\Integration\Validator;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Webgriffe\SyliusElasticsearchPlugin\Validator\RequestValidatorInterface;

final class RequestValidatorTest extends KernelTestCase
{
    private RequestValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = self::getContainer()->get('webgriffe.sylius_elasticsearch_plugin.validator.request');
    }

    public function test_it_do_not_throw_when_request_has_valid_parameters(): void
    {
        $request = new Request(['sorting' => ['createdAt' => 'asc'], 'page' => 1, 'limit' => 1, 'filters' => ['name' => 'foo']]);

        $this->validator->validate($request);

        self::assertTrue(true);
    }

    public function test_it_throws_when_request_has_bad_direction_in_sorting_parameter(): void
    {
        $request = new Request(['sorting' => ['createdAt' => 'NOT_VALID']]);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Unexpected value for parameter "sorting": expecting "asc" or "desc", got "NOT_VALID".');

        $this->validator->validate($request);
    }

    public function test_it_throws_when_request_has_non_array_sorting_parameter(): void
    {
        $request = new Request(['sorting' => 'NOT_VALID']);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Unexpected value for parameter "sorting": expecting "array", got "string".');

        $this->validator->validate($request);
    }

    public function test_it_throws_when_request_has_string_page_parameter(): void
    {
        $request = new Request(['page' => 'NOT_AN_INTEGER']);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Unexpected value for parameter "page": expecting "integer", got "string".');

        $this->validator->validate($request);
    }

    public function test_it_throws_when_request_has_array_page_parameter(): void
    {
        $request = new Request(['page' => ['NOT_AN_INTEGER']]);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Input value "page" contains a non-scalar value.');

        $this->validator->validate($request);
    }

    public function test_it_throws_when_request_has_string_limit_parameter(): void
    {
        $request = new Request(['limit' => 'NOT_AN_INTEGER']);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Unexpected value for parameter "limit": expecting "integer", got "string".');

        $this->validator->validate($request);
    }

    public function test_it_throws_when_request_has_array_limit_parameter(): void
    {
        $request = new Request(['limit' => ['NOT_AN_INTEGER']]);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Input value "limit" contains a non-scalar value.');

        $this->validator->validate($request);
    }

    public function test_it_throws_when_request_has_non_array_filters_parameter(): void
    {
        $request = new Request(['filters' => 'NOT_AN_ARRAY']);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Unexpected value for parameter "filters": expecting "array", got "string".');

        $this->validator->validate($request);
    }
}
