<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_edit_sale_and_stock_prices_and_totals_are_updated(): void
    {
        $fixture = $this->createSaleFixture(currentStock: 8, soldAmount: 2);
        $newCustomer = Customer::create([
            'name' => 'Cliente nuevo',
            'dni' => '22222222',
        ]);
        $newMedicine = Medicine::create([
            'category_id' => $fixture['category']->id,
            'name' => 'Medicamento nuevo',
            'price' => 5.00,
            'stock' => 20,
        ]);

        $editResponse = $this->actingAs($fixture['administrator'])
            ->get(route('sales.edit', $fixture['sale']));

        $editResponse->assertOk();

        $this->actingAs($fixture['seller'])
            ->get(route('sales.create'))
            ->assertOk();

        $response = $this->actingAs($fixture['administrator'])
            ->put(route('sales.update', $fixture['sale']), [
                'customer_id' => $newCustomer->id,
                'items' => [
                    ['medicine_id' => $fixture['medicine']->id, 'amount' => 3],
                    ['medicine_id' => $newMedicine->id, 'amount' => 1],
                ],
            ]);

        $response->assertRedirect(route('sales.show', $fixture['sale']));

        $this->assertDatabaseHas('sales', [
            'id' => $fixture['sale']->id,
            'customer_id' => $newCustomer->id,
            'user_id' => $fixture['seller']->id,
            'total' => 10.97,
            'igv' => 1.67,
        ]);
        $this->assertDatabaseHas('medicines', [
            'id' => $fixture['medicine']->id,
            'stock' => 7,
        ]);
        $this->assertDatabaseHas('medicines', [
            'id' => $newMedicine->id,
            'stock' => 19,
        ]);
        $this->assertDatabaseHas('detail_sales', [
            'sale_id' => $fixture['sale']->id,
            'medicine_id' => $fixture['medicine']->id,
            'amount' => 3,
            'price_unit' => 1.99,
            'subtotal' => 5.97,
        ]);
        $this->assertDatabaseHas('detail_sales', [
            'sale_id' => $fixture['sale']->id,
            'medicine_id' => $newMedicine->id,
            'amount' => 1,
            'price_unit' => 5.00,
            'subtotal' => 5.00,
        ]);
    }

    public function test_only_administrator_can_open_or_update_a_sale(): void
    {
        $fixture = $this->createSaleFixture(currentStock: 8, soldAmount: 2);

        $this->actingAs($fixture['seller'])
            ->get(route('sales.edit', $fixture['sale']))
            ->assertForbidden();

        $this->actingAs($fixture['seller'])
            ->put(route('sales.update', $fixture['sale']), [
                'customer_id' => $fixture['customer']->id,
                'items' => [
                    ['medicine_id' => $fixture['medicine']->id, 'amount' => 2],
                ],
            ])
            ->assertForbidden();
    }

    public function test_insufficient_stock_rolls_back_the_sale_and_inventory_changes(): void
    {
        $fixture = $this->createSaleFixture(currentStock: 0, soldAmount: 2);

        $response = $this->actingAs($fixture['administrator'])
            ->from(route('sales.edit', $fixture['sale']))
            ->put(route('sales.update', $fixture['sale']), [
                'customer_id' => $fixture['customer']->id,
                'items' => [
                    ['medicine_id' => $fixture['medicine']->id, 'amount' => 3],
                ],
            ]);

        $response->assertRedirect(route('sales.edit', $fixture['sale']));
        $response->assertSessionHasErrors('items');

        $this->assertDatabaseHas('sales', [
            'id' => $fixture['sale']->id,
            'total' => 3.98,
        ]);
        $this->assertDatabaseHas('medicines', [
            'id' => $fixture['medicine']->id,
            'stock' => 0,
        ]);
        $this->assertDatabaseHas('detail_sales', [
            'sale_id' => $fixture['sale']->id,
            'medicine_id' => $fixture['medicine']->id,
            'amount' => 2,
            'subtotal' => 3.98,
        ]);
    }

    private function createSaleFixture(int $currentStock, int $soldAmount): array
    {
        $administrator = $this->createUser('Administrador', 'admin@example.test', '12345678');
        $seller = $this->createUser('Cajero', 'cashier@example.test', '87654321');
        $customer = Customer::create([
            'name' => 'Cliente inicial',
            'dni' => '11111111',
        ]);
        $category = Category::create([
            'name' => 'Categoría de prueba',
            'description' => 'Categoría para pruebas de edición de ventas',
        ]);
        $medicine = Medicine::create([
            'category_id' => $category->id,
            'name' => 'Medicamento histórico',
            'price' => 4.50,
            'stock' => $currentStock,
        ]);
        $subtotal = 1.99 * $soldAmount;
        $sale = Sale::create([
            'customer_id' => $customer->id,
            'user_id' => $seller->id,
            'date' => now(),
            'total' => $subtotal,
            'igv' => round($subtotal * 18 / 118, 2),
        ]);
        $sale->details()->create([
            'medicine_id' => $medicine->id,
            'amount' => $soldAmount,
            'price_unit' => 1.99,
            'subtotal' => $subtotal,
        ]);

        return compact('administrator', 'seller', 'customer', 'category', 'medicine', 'sale');
    }

    private function createUser(string $roleName, string $email, string $dni): User
    {
        $role = Role::create([
            'name' => $roleName,
            'description' => "Rol de prueba: {$roleName}",
        ]);

        return User::create([
            'role_id' => $role->id,
            'name' => "Usuario {$roleName}",
            'dni' => $dni,
            'email' => $email,
            'password' => 'password',
        ]);
    }
}
