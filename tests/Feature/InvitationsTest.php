<?php

namespace Tests\Feature;

use App\User;
use App\Project;
use Tests\TestCase;
use Facades\Tests\Setup\ProjectFactory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvitationsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function non_owners_may_not_invite_users()
    {
        $project = ProjectFactory::create();

        $user = factory(User::class)->create();

        $assertInvitationForbiden = function() use ($user, $project) {
            $this->actingAs($user)
                ->post($project->path() . '/invitations')
                ->assertStatus(403);
        };

        $assertInvitationForbiden();

        $project->invite($user);

        $assertInvitationForbiden();
    }

    /** @test */
    public function a_project_can_invite_a_user()
    {
        $this->withoutExceptionHandling();

        $project = ProjectFactory::create();

        $userToInvite = factory(User::class)->create();

        $this->actingAs($project->owner)
            ->post($project->path() . '/invitations', [
                'email' => $userToInvite->email,
            ]);
        
        $this->assertTrue($project->members->contains($userToInvite));
    }

    /** @test */
    public function invited_users_may_update_project_details()
    {
        $project = ProjectFactory::create();

        $project->invite($newUser = factory(User::class)->create());

        $this->signIn($newUser);

        $this->post(
            action('ProjectTasksController@store', $project),
            $task = ['body' => 'Foo Task']
        );
        
        $this->assertDatabaseHas('tasks', $task);
    }

    /** @test */
    public function the_email_address_must_be_associated_with_a_valid_birdboard_account()
    {
        $project = ProjectFactory::create();

        
        $this->actingAs($project->owner)
            ->post($project->path() . '/invitations', [
                'email' => 'nouqp@example.com',
            ])
            ->assertSessionHasErrors(
                ['email' => 'The user you are inviting must have a Birdboard account.'],
                null,
                $errorBag = 'invitations'
            );
    }
}
