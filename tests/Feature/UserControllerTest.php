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

it('can list users', function () {
    actingAs($this->adminUser);

    User::factory()->count(5)->withCompany($this->company->id)->create([
        'role_id' => 2,
    ]);

    $response = get('/api/users?per_page=2');

    $response->assertStatus(200)
        ->assertJsonStructure(['status', 'data' => ['data']]);
});

it('can find a user', function () {
    actingAs($this->adminUser);

    $user = User::factory()->withCompany($this->company->id)->create(['role_id' => 2]);

    $response = get("/api/users/{$user->id}");

    $response->assertStatus(200)
        ->assertJson(['status' => true, 'data' => $user->toArray()]);
});

it('can create a user', function () {
    actingAs($this->adminUser);

    $data = [
        'name' => 'Manager two',
        'email' => 'managertwo@companya.com',
        'phone' => '1234567890',
        'address' => 'Malang, Jawa Timur',
        'role_id' => $this->managerRole->id,
        'company_id' => $this->company->id,
    ];

    $response = post('/api/users', $data);

    $response->assertStatus(200)
        ->assertJson(['status' => true]);

    $this->assertDatabaseHas('users', ['email' => 'managertwo@companya.com']);
});

it('can update a user', function () {
    actingAs($this->adminUser);

    $user = User::factory()->withCompany($this->company->id)->create(['role_id' => 2]);

    $data = [
        'name' => 'Manager two updated',
        'email' => 'managertwoupdate@companya.com',
        'phone' => '1234567890',
        'address' => 'Malang, Jawa Timur',
        'role_id' => $this->managerRole->id,
    ];

    $response = put("/api/users/{$user->id}", $data);

    $response->assertStatus(200)
        ->assertJson(['status' => true]);

    $this->assertDatabaseHas('users', ['email' => 'managertwoupdate@companya.com']);
});

it('can delete a user', function () {
    actingAs($this->adminUser);

    $user = User::factory()->withCompany($this->company->id)->create(['role_id' => 2]);

    $response = delete("/api/users/{$user->id}");

    $response->assertStatus(200)
        ->assertJson(['status' => true]);

    $this->assertSoftDeleted('users', ['id' => $user->id]);
});