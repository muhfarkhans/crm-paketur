<?php

use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use App\Models\CompanyUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use function Pest\Laravel\{actingAs, get, post, put, delete};

uses(RefreshDatabase::class);

beforeEach(function () {
    Schema::disableForeignKeyConstraints();
    User::truncate();
    Role::truncate();
    Company::truncate();
    CompanyUser::truncate();
    Schema::enableForeignKeyConstraints();

    $this->company = Company::create(['name' => 'Company A', 'email' => 'mail@companya.com', "phone" => "081268712631"]);

    $this->adminRole = Role::create(['id' => 1, 'name' => 'admin']);
    $this->managerRole = Role::create(['id' => 2, 'name' => 'manager']);
    $this->employeeRole = Role::create(['id' => 3, 'name' => 'employee']);

    $this->adminUser = User::create([
        'name' => 'admin',
        'email' => 'admin@admin.com',
        'password' => bcrypt('password'),
        'role_id' => 1,
        'phone' => "+62876198762",
        'address' => "Bantul, Yogyakarta",
    ]);

    $this->managerUser = User::create([
        'name' => 'manager',
        'email' => 'manager@companya.com',
        'password' => bcrypt('password'),
        'role_id' => 2,
        'phone' => "+62876198762",
        'address' => "Bantul, Yogyakarta",
    ]);

    CompanyUser::create([
        'user_id' => $this->managerUser->id,
        'company_id' => $this->company->id,
    ]);

    $this->employeeUser = User::create([
        'name' => 'employee',
        'email' => 'employee@companya.com',
        'password' => bcrypt('password'),
        'role_id' => 3,
        'phone' => "+62876198762",
        'address' => "Bantul, Yogyakarta",
    ]);
});

it('can list companies', function () {
    actingAs($this->adminUser);

    Company::factory()->count(5)->create();

    $response = get('/api/companies?per_page=2');

    $response->assertStatus(200)
        ->assertJsonStructure(['status', 'data' => ['data']]);
});

it('can find a company', function () {
    actingAs($this->adminUser);

    $company = Company::factory()->create();

    $response = get("/api/companies/{$company->id}");

    $response->assertStatus(200)
        ->assertJson(['status' => true, 'data' => $company->toArray()]);
});

it('can create a company', function () {
    actingAs($this->adminUser);

    $data = [
        'name' => 'Company B',
        'email' => 'manager@companyb.com',
        'phone' => '12345678909',
    ];

    $response = post('/api/companies', $data);

    $response->assertStatus(200)
        ->assertJson(['status' => true]);

    $this->assertDatabaseHas('companies', ['email' => 'manager@companyb.com']);
});

it('can update a company', function () {
    actingAs($this->adminUser);

    $company = Company::factory()->create();

    $data = [
        'name' => 'Company B update',
        'email' => 'managerupdate@companyb.com',
        'phone' => '12345678909',
    ];

    $response = put("/api/companies/{$company->id}", $data);

    $response->assertStatus(200)
        ->assertJson(['status' => true]);

    $this->assertDatabaseHas('companies', ['email' => 'managerupdate@companyb.com']);
});

it('can delete a company', function () {
    actingAs($this->adminUser);

    $company = Company::factory()->create();

    $response = delete("/api/companies/{$company->id}");

    $response->assertStatus(200)
        ->assertJson(['status' => true]);

    $this->assertSoftDeleted('companies', ['id' => $company->id]);
});