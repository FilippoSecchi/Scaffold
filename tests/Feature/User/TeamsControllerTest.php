<?php

namespace Tests\Feature\User;

use Tests\TestCase;

class TeamsControllerTest extends TestCase
{
    public function testIndex()
    {
        $response = $this->get(route('user.teams'));

        $response->assertOk();
        $response->assertSee('Create Team');
    }
}
