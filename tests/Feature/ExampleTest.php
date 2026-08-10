<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Perilaku route root (/) — portal multi-role:
 * guest diarahkan ke login, user yang sudah login diarahkan ke dashboard.
 */
class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    #[Test]
    public function guest_di_root_redirect_ke_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    #[Test]
    public function user_yang_login_di_root_redirect_ke_dashboard(): void
    {
        $user = User::where('email', 'admin@bapperida-indramayu.go.id')->firstOrFail();

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect(route('dashboard'));
    }
}