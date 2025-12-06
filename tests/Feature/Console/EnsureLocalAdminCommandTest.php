<?php

namespace Tests\Feature\Console;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EnsureLocalAdminCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('role')->default('user');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_creates_admin_when_not_exists(): void
    {
        Artisan::call('user:ensure-admin', [
            '--email' => 'local-admin@example.com',
            '--password' => 'secret123',
            '--name' => 'Local Admin',
        ]);

        $user = User::where('email', 'local-admin@example.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->is_active);
        $this->assertEquals('admin', $user->role);
        $this->assertTrue(password_verify('secret123', $user->password));
    }

    public function test_updates_existing_admin(): void
    {
        $user = User::factory()->create([
            'email' => 'local-admin@example.com',
            'role' => 'user',
            'is_active' => false,
        ]);

        Artisan::call('user:ensure-admin', [
            '--email' => 'local-admin@example.com',
            '--password' => 'newpass456',
            '--name' => 'Updated Admin',
        ]);

        $user->refresh();

        $this->assertEquals('Updated Admin', $user->name);
        $this->assertEquals('admin', $user->role);
        $this->assertTrue($user->is_active);
        $this->assertTrue(password_verify('newpass456', $user->password));
    }
}
