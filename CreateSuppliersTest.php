<?php

namespace Tests\Feature\Controllers\Api\Supplier;

use App\Currency;
use App\Supplier;
use Tests\TestDatabaseCase;

/** @testdox Feature: Create Suppliers */
class CreateSuppliersTest extends TestDatabaseCase
{
    var $url;

    protected function setUp()
    {
        parent::setUp();

        $this->url = '/api/suppliers';
        $this->createCurrencies();
        $this->createCountries();
    }

    /** @test */
    function authorized_user_can_create_a_supplier()
    {
        $this->signInAsAdmin();

        $fields = factory(Supplier::class)->make()->toArray();

        $supplier = $this->postJson($this->url, $fields)
            ->assertSuccessful()
            ->json();

        $this->assertArrayHasKey('id', $supplier);
    }

    /** @test */
    function requires_a_company_name()
    {
        $this->signInAsAdmin();

        $errors = $this->postJson(
            $this->url,
            $this->validFields(['company_name' => ''])
        )
            ->assertStatus(422)
            ->json('errors');

        $this->assertArrayHasKey('company_name', $errors);
    }

    /** @test */
    function validates_currency_id()
    {
        $this->signInAsAdmin();

        $errors = $this->postJson(
            $this->url,
            $this->validFields(['currency_id' => 0])
        )
            ->assertStatus(422)
            ->json('errors');

        $this->assertArrayHasKey('currency_id', $errors);
    }

    /** @test */
    function validates_email()
    {
        $this->signInAsAdmin();

        $errors = $this->postJson(
            $this->url,
            $this->validFields(['email' => 'not-valid-email'])
        )
            ->assertStatus(422)
            ->json('errors');

        $this->assertArrayHasKey('email', $errors);

        Supplier::create($this->validFields());

        $errors = $this->postJson(
            $this->url,
            $this->validFields()
        )
            ->assertStatus(422)
            ->json('errors');

        $this->assertArrayHasKey('email', $errors);
    }

    /** @test */
    function unauthorized_user_may_not_create_a_supplier()
    {
        $this->postJson($this->url, $this->validFields())
            ->assertStatus(401);

        $this->signInAsCustomer();

        $this->postJson($this->url, $this->validFields())
            ->assertStatus(401);
    }

    /**
     * @param array $overrides
     * @return array
     */
    function validFields($overrides = []): array
    {
        return array_merge([
            'company_name' => 'vendor',
            'currency_id' => Currency::first()->id,
            'email' => 'supplier@example.com',
        ], $overrides);
    }
}
