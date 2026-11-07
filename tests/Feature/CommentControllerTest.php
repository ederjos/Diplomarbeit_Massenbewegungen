<?php

use App\Models\Comment;
use App\Models\Measurement;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

describe('comment management', function () {
    it('allows a comment-capable project member to comment on a measurement', function () {
        /** @var TestCase $this */
        $role = Role::factory()->createOne(['can_comment' => true]);
        $user = User::factory()->createOne(['role_id' => $role->id]);
        $project = Project::factory()->createOne();
        $measurement = Measurement::factory()->createOne(['project_id' => $project->id]);
        $project->users()->attach($user->id);

        $response = $this->actingAs($user)->post(route('project.measurements.comments.store', [$project, $measurement]), [
            'content' => 'The slope is still moving.',
        ]);

        $response->assertRedirect(route('project', $project));
        $this->assertDatabaseHas('comments', [
            'measurement_id' => $measurement->id,
            'user_id' => $user->id,
            'content' => 'The slope is still moving.',
        ]);
    });

    it('allows admins to create comments without the comment permission', function () {
        /** @var TestCase $this */
        $role = Role::factory()->createOne(['is_admin' => true, 'can_comment' => false]);
        $admin = User::factory()->createOne(['role_id' => $role->id]);
        $project = Project::factory()->createOne();
        $measurement = Measurement::factory()->createOne(['project_id' => $project->id]);
        $project->users()->attach($admin->id);

        $response = $this->actingAs($admin)->post(route('project.measurements.comments.store', [$project, $measurement]), [
            'content' => 'Admin note.',
        ]);

        $response->assertRedirect(route('project', $project));
        $this->assertDatabaseHas('comments', ['user_id' => $admin->id, 'content' => 'Admin note.']);
    });

    it('allows authors to update and delete their own comments', function () {
        /** @var TestCase $this */
        $user = User::factory()->createOne();
        $project = Project::factory()->createOne();
        $measurement = Measurement::factory()->createOne(['project_id' => $project->id]);
        $comment = Comment::factory()->createOne(['measurement_id' => $measurement->id, 'user_id' => $user->id]);
        $project->users()->attach($user->id);

        $updateResponse = $this->actingAs($user)->put(route('project.measurements.comments.update', [$project, $measurement, $comment]), [
            'content' => 'Updated note.',
        ]);
        $deleteResponse = $this->actingAs($user)->delete(route('project.measurements.comments.destroy', [$project, $measurement, $comment]));

        $updateResponse->assertRedirect(route('project', $project));
        $deleteResponse->assertRedirect(route('project', $project));
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    });

    it('allows admins to update and delete another users comment', function () {
        /** @var TestCase $this */
        $adminRole = Role::factory()->createOne(['is_admin' => true]);
        $admin = User::factory()->createOne(['role_id' => $adminRole->id]);
        $author = User::factory()->createOne();
        $project = Project::factory()->createOne();
        $measurement = Measurement::factory()->createOne(['project_id' => $project->id]);
        $comment = Comment::factory()->createOne(['measurement_id' => $measurement->id, 'user_id' => $author->id]);
        $project->users()->attach($admin->id);

        $response = $this->actingAs($admin)->put(route('project.measurements.comments.update', [$project, $measurement, $comment]), [
            'content' => 'Moderated note.',
        ]);

        $response->assertRedirect(route('project', $project));
        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'content' => 'Moderated note.']);
        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'last_edited_by_user_id' => $admin->id]);

        $deleteResponse = $this->actingAs($admin)->delete(route('project.measurements.comments.destroy', [$project, $measurement, $comment]));

        $deleteResponse->assertRedirect(route('project', $project));
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    });

    it('forbids non-owners from changing comments', function () {
        /** @var TestCase $this */
        $author = User::factory()->createOne();
        $otherUser = User::factory()->createOne();
        $project = Project::factory()->createOne();
        $measurement = Measurement::factory()->createOne(['project_id' => $project->id]);
        $comment = Comment::factory()->createOne(['measurement_id' => $measurement->id, 'user_id' => $author->id]);
        $project->users()->attach($otherUser->id);

        $response = $this->actingAs($otherUser)->put(route('project.measurements.comments.update', [$project, $measurement, $comment]), [
            'content' => 'Not allowed.',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'content' => $comment->content]);
    });

    it('rejects empty comment content', function () {
        /** @var TestCase $this */
        $role = Role::factory()->createOne(['can_comment' => true]);
        $user = User::factory()->createOne(['role_id' => $role->id]);
        $project = Project::factory()->createOne();
        $measurement = Measurement::factory()->createOne(['project_id' => $project->id]);
        $project->users()->attach($user->id);

        $response = $this->actingAs($user)->post(route('project.measurements.comments.store', [$project, $measurement]), ['content' => '']);

        $response->assertSessionHasErrors('content');
        $this->assertDatabaseCount('comments', 0);
    });

    it('does not allow a measurement from another project in a comment route', function () {
        /** @var TestCase $this */
        $role = Role::factory()->createOne(['can_comment' => true]);
        $user = User::factory()->createOne(['role_id' => $role->id]);
        $project = Project::factory()->createOne();
        $otherProject = Project::factory()->createOne();
        $measurement = Measurement::factory()->createOne(['project_id' => $otherProject->id]);
        $project->users()->attach($user->id);

        $response = $this->actingAs($user)->post(route('project.measurements.comments.store', [$project, $measurement]), [
            'content' => 'Cross-project note.',
        ]);

        $response->assertNotFound();
        $this->assertDatabaseCount('comments', 0);
    });
});
