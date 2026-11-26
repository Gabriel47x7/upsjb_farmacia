<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.locale' => 'es']);
    }

    public function test_sale_number_links_to_its_detail_from_the_report(): void
    {
        $administrator = $this->createAdministrator();
        $customer = Customer::create([
            'name' => 'Cliente de prueba',
            'dni' => '87654321',
        ]);
        $sale = Sale::create([
            'customer_id' => $customer->id,
            'user_id' => $administrator->id,
            'date' => now(),
            'total' => 100,
            'igv' => 15.25,
        ]);

        $response = $this->actingAs($administrator)->get(route('reports.sales'));

        $response
            ->assertOk()
            ->assertSee(route('sales.show', $sale), false)
            ->assertSee(str_pad((string) $sale->id, 6, '0', STR_PAD_LEFT));
    }

    public function test_invalid_date_range_shows_the_spanish_validation_error(): void
    {
        $administrator = $this->createAdministrator();

        $response = $this->actingAs($administrator)
            ->from(route('reports.sales'))
            ->followingRedirects()
            ->get(route('reports.sales', [
                'from' => '2026-11-20',
                'to' => '2026-11-19',
            ]));

        $response
            ->assertOk()
            ->assertSee('El campo fecha final debe ser una fecha igual o posterior a fecha inicial.');
    }

    private function createAdministrator(): User
    {
        $role = Role::create([
            'name' => 'Administrador',
            'description' => 'Acceso total al sistema',
        ]);

        return User::create([
            'role_id' => $role->id,
            'name' => 'Administrador de prueba',
            'dni' => '12345678',
            'email' => 'admin@example.test',
            'password' => 'password',
        ]);
    }
}
