<?php

namespace IlBronza\Processes\Tests\Feature;

use IlBronza\Processes\Models\Process;
use IlBronza\Processes\Models\ProcessStatus;
use IlBronza\Processes\Models\ProcessStatusTransition;
use IlBronza\Processes\Tests\Fixtures\ProcessController;
use IlBronza\Processes\Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

class ProcessCrudTest extends TestCase
{
    protected function defineRoutes($router)
    {
        Route::post('/test/processes', [ProcessController::class, 'store']);
        Route::put('/test/processes/{process}', [ProcessController::class, 'update']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Gate::define('write-process', fn () => true);
        $user = new \App\Models\User(['name' => 'Tester']);
        $user->id = 'actor';
        $this->actingAs($user);
    }

    private function payload(array $attributes = []): array
    {
        return array_merge(['process_type_id' => $this->type->id, 'process_status_id' => $this->draft->id], $attributes);
    }

    public function test_store_uses_default_status_and_initial_log(): void
    {
        $this->postJson('/test/processes', ['process_type_id' => $this->type->id, 'status_reason' => 'Created'])->assertRedirect('/test/processes');
        $process = Process::firstOrFail();
        $this->assertSame($this->draft->id, $process->process_status_id);
        $this->assertSame('Created', DB::table('processes__process_status_logs')->value('reason'));
        $this->assertSame('actor', DB::table('processes__process_status_logs')->value('user_id'));
    }

    public function test_store_rejects_missing_required_resources_without_writing(): void
    {
        $this->type->update(['requires_operator' => true]);
        $this->postJson('/test/processes', $this->payload())->assertUnprocessable()->assertJsonValidationErrors('operator_id');
        $this->assertSame(0, Process::count());
        $this->assertSame(0, DB::table('processes__process_status_logs')->count());
    }

    public function test_update_rejects_overlap_and_invalid_range(): void
    {
        $this->process(['operator_id' => 'busy', 'starts_at' => '2026-10-16 09:00', 'ends_at' => '2026-10-16 10:00']);
        $other = $this->process(['operator_id' => 'free', 'starts_at' => '2026-10-16 09:00', 'ends_at' => '2026-10-16 10:00']);
        $url = '/test/processes/' . $other->id;
        $this->putJson($url, $this->payload(['operator_id' => 'busy']))->assertUnprocessable()->assertJsonValidationErrors('operator_id');
        $this->putJson($url, $this->payload(['ends_at' => '2026-10-16 08:00']))->assertUnprocessable()->assertJsonValidationErrors('ends_at');
        $this->assertSame('free', $other->fresh()->operator_id);
        $this->assertSame('10:00', $other->fresh()->ends_at->format('H:i'));
    }

    public function test_status_reason_is_saved_as_log_and_failure_rolls_back_fields(): void
    {
        $process = $this->process();
        $final = ProcessStatus::create(['name' => 'done', 'label' => 'Done', 'is_active' => true]);
        ProcessStatusTransition::create(['from_status_id' => $this->draft->id, 'to_status_id' => $final->id, 'is_allowed' => true, 'requires_reason' => true]);
        $url = '/test/processes/' . $process->id;
        $payload = $this->payload(['process_status_id' => $final->id, 'notes' => 'Changed', 'closed_at' => '2026-10-16 12:00']);
        $this->putJson($url, $payload)->assertUnprocessable()->assertJsonValidationErrors('process_status_id');
        $this->assertNull($process->fresh()->notes);
        $this->assertNull($process->fresh()->closed_at);
        $this->putJson($url, array_merge($payload, ['status_reason' => 'Done']))->assertRedirect();
        $this->assertSame($final->id, $process->fresh()->process_status_id);
        $this->assertSame('actor', $process->fresh()->closed_by_user_id);
        $this->assertSame(1, DB::table('processes__process_status_logs')->where('reason', 'Done')->count());
    }

    public function test_crud_lock_unlock_and_authorization(): void
    {
        $this->postJson('/test/processes', $this->payload(['is_locked' => true]))->assertRedirect();
        $process = Process::firstOrFail();
        $this->assertSame('actor', $process->locked_by_user_id);
        $url = '/test/processes/' . $process->id;
        $this->putJson($url, $this->payload(['notes' => 'Blocked']))->assertUnprocessable()->assertJsonValidationErrors('is_locked');
        Gate::define('write-process', fn () => false);
        $this->putJson($url, $this->payload(['is_locked' => false]))->assertForbidden();
        $this->assertTrue($process->fresh()->is_locked);
        Gate::define('write-process', fn () => true);
        $this->putJson($url, $this->payload(['is_locked' => false, 'notes' => 'Unlocked']))->assertRedirect();
        $this->assertFalse($process->fresh()->is_locked);
        $this->assertNull($process->fresh()->locked_by_user_id);
        $this->assertSame('Unlocked', $process->fresh()->notes);
    }

    public function test_crud_parent_cannot_create_a_cycle(): void
    {
        $parent = $this->process();
        $child = $this->process(['parent_id' => $parent->id]);
        $this->putJson('/test/processes/' . $parent->id, $this->payload(['parent_id' => $child->id]))
            ->assertUnprocessable()->assertJsonValidationErrors('parent_id');
    }

    public function test_inline_and_batch_editors_use_the_same_lifecycle_rules(): void
    {
        $process = $this->process();
        $url = '/test/processes/' . $process->id;
        $this->putJson($url, ['ib-editor' => true, 'field' => 'is_locked', 'value' => true])->assertOk()->assertJsonPath('value', true);
        $this->assertSame('actor', $process->fresh()->locked_by_user_id);
        $this->putJson($url, ['ib-editor' => true, 'ib-editor-batch' => true, 'fields' => ['is_locked' => false, 'notes' => 'Batch']])->assertOk();
        $this->assertFalse($process->fresh()->is_locked);
        $this->assertSame('Batch', $process->fresh()->notes);
        $this->putJson($url, ['ib-editor' => true, 'field' => 'parent_id', 'value' => $process->id])->assertUnprocessable()->assertJsonValidationErrors('parent_id');
    }
    public function test_inline_status_change_accepts_reason_and_enforces_transition_permission(): void
    {
        $process = $this->process();
        $final = ProcessStatus::create(['name' => 'done', 'label' => 'Done', 'is_active' => true]);
        ProcessStatusTransition::create(['from_status_id' => $this->draft->id, 'to_status_id' => $final->id,
            'is_allowed' => true, 'requires_reason' => true, 'requires_permission' => 'finish']);
        $url = '/test/processes/' . $process->id;
        $payload = ['ib-editor' => true, 'field' => 'process_status_id', 'value' => $final->id, 'status_reason' => 'Finished'];
        Gate::define('finish', fn () => false);
        $this->putJson($url, $payload)->assertUnprocessable()->assertJsonValidationErrors('process_status_id');
        $this->assertSame($this->draft->id, $process->fresh()->process_status_id);
        Gate::define('finish', fn () => true);
        $this->putJson($url, $payload)->assertOk();
        $this->assertSame($final->id, $process->fresh()->process_status_id);
        $this->assertSame(1, DB::table('processes__process_status_logs')->where('reason', 'Finished')->count());
    }

    public function test_batch_invalid_status_rolls_back_unlock_and_other_fields(): void
    {
        $process = $this->process(['is_locked' => true]);
        $final = ProcessStatus::create(['name' => 'done', 'label' => 'Done', 'is_active' => true]);
        $this->putJson('/test/processes/' . $process->id, [
            'ib-editor' => true, 'ib-editor-batch' => true,
            'fields' => ['is_locked' => false, 'notes' => 'Invalid', 'process_status_id' => $final->id],
        ])->assertUnprocessable()->assertJsonValidationErrors('process_status_id');
        $this->assertTrue($process->fresh()->is_locked);
        $this->assertNull($process->fresh()->notes);
        $this->assertSame(1, $process->statusLogs()->count());
    }

}
