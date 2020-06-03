<?php

namespace Tests;

use App\Http\Middleware\Admin;
use App\Http\Middleware\Permissions;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    public $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            VerifyCsrfToken::class,
            EnsureEmailIsVerified::class,
            Admin::class,
            Permissions::class,
        ]);

        $role = factory(Role::class)->create([
            'name' => 'admin',
            'label' => 'Admin',
        ]);

        $this->user = factory(User::class)->create();
        $this->user->roles()->attach($role->id);

        $this->be($this->user);
    }
}
