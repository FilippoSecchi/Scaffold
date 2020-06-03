<?php

namespace Tests\Feature\User;

use App\Models\Invite;
use App\Models\Team;
use App\Models\User;
use Tests\TestCase;

class InvitesControllerTest extends TestCase
{
    public function testIndex()
    {
        $response = $this->get(route('user.invites'));

        $response->assertOk();
    }

    public function testAccept()
    {
        $user = factory(User::class)->create();
        $team = factory(Team::class)->create();

        $invite = factory(Invite::class)->create([
            'user_id' => $user->id,
            'email' => $this->user->email,
            'relationship' => 'teamMemberships',
            'model_id' => $team->id,
            'message' => 'new team!',
            'token' => 'foo',
        ]);

        $response = $this->post(route('user.invites.accept', $invite));

        $response->assertStatus(302);
        $response->assertSessionHas('message', 'Invitation accepted');
    }

    public function testReject()
    {
        $user = factory(User::class)->create();
        $team = factory(Team::class)->create();

        $invite = factory(Invite::class)->create([
            'user_id' => $user->id,
            'email' => $this->user->email,
            'relationship' => 'teamMemberships',
            'model_id' => $team->id,
            'message' => 'new team!',
            'token' => 'foo',
        ]);

        $response = $this->post(route('user.invites.reject', $invite));

        $response->assertStatus(302);
        $response->assertSessionHas('message', 'Invitation rejected');
    }
}
