<?php

namespace IlBronza\Processes\Tests\Feature;

use IlBronza\Processes\Helpers\ProcessCloserHelper;
use IlBronza\Processes\Helpers\ProcessCreatorHelper;
use IlBronza\Processes\Helpers\ProcessLockerHelper;
use IlBronza\Processes\Helpers\ProcessUpdaterHelper;
use IlBronza\Processes\Models\Process;
use IlBronza\Processes\Models\ProcessStatus;
use IlBronza\Processes\Models\ProcessStatusTransition;
use IlBronza\Processes\Tests\TestCase;
use Illuminate\Support\Facades\DB;
use LogicException;

class ProcessLifecycleTest extends TestCase
{
    public function test_creation_resolves_aliases_and_records_initial_status(): void
    {
        $process = ProcessCreatorHelper::create(['type' => 'test', 'status' => 'draft'], ['reason' => 'Importazione']);
        $this->assertSame($this->draft->id, $process->process_status_id);
        $this->assertSame(1, DB::table('processes__process_status_logs')->count());
        $this->assertSame('Importazione', $process->getLastStatusLog()->reason);
    }

    public function test_requirements_apply_on_create_and_update(): void
    {
        $process = $this->process();
        $this->type->update(['requires_operator' => true]);
        $this->assertInvalid('operator_id', fn () => $this->process());
        $this->assertInvalid('operator_id', fn () => ProcessUpdaterHelper::update($process, ['notes' => 'invalid']));
        $this->assertNull($process->fresh()->notes);
        $this->assertSame(1, Process::count());
    }

    public function test_inactive_type_and_status_cannot_be_assigned_at_creation(): void
    {
        $this->type->update(['is_active' => false]);
        $this->assertInvalid('process_type_id', fn () => $this->process());
        $this->type->update(['is_active' => true]);
        $this->draft->update(['is_active' => false]);
        $this->assertInvalid('process_status_id', fn () => $this->process());
    }

    public function test_ranges_and_morph_pairs_are_validated(): void
    {
        $this->assertInvalid('ends_at', fn () => $this->process(['starts_at' => '2026-10-16 10:00', 'ends_at' => '2026-10-16 09:00']));
        $this->assertInvalid('planned_ends_at', fn () => $this->process(['planned_starts_at' => '2026-10-16 10:00', 'planned_ends_at' => '2026-10-16 09:00']));
        $this->assertInvalid('starts_at', fn () => $this->process(['ends_at' => '2026-10-16 09:00']));
        $this->assertInvalid('toolable_id', fn () => $this->process(['toolable_type' => 'machine']));
    }

    public function test_actual_and_planned_overlaps_are_rejected_but_adjacent_slots_are_allowed(): void
    {
        foreach(['', 'planned_'] as $prefix)
        {
            $operator = $prefix . 'operator';
            $this->process(['operator_id' => $operator, $prefix . 'starts_at' => '2026-10-16 09:00', $prefix . 'ends_at' => '2026-10-16 10:00']);
            $this->assertInvalid('operator_id', fn () => $this->process(['operator_id' => $operator, $prefix . 'starts_at' => '2026-10-16 09:30', $prefix . 'ends_at' => '2026-10-16 10:30']));
            $adjacent = $this->process(['operator_id' => $operator, $prefix . 'starts_at' => '2026-10-16 10:00', $prefix . 'ends_at' => '2026-10-16 11:00']);
            $this->assertNotNull($adjacent->id);
        }
    }

    public function test_open_intervals_block_later_slots_and_ignore_the_process_being_updated(): void
    {
        $open = $this->process(['operator_id' => 'operator', 'starts_at' => '2026-10-16 09:00']);
        $this->assertInvalid('operator_id', fn () => $this->process(['operator_id' => 'operator', 'starts_at' => '2026-10-16 10:00', 'ends_at' => '2026-10-16 11:00']));
        $updated = ProcessUpdaterHelper::update($open, ['ends_at' => '2026-10-16 10:00']);
        $this->assertSame(3600, $updated->duration_seconds);
        $this->assertNotNull($this->process(['operator_id' => 'operator', 'starts_at' => '2026-10-16 10:00'])->id);
    }

    public function test_changing_resources_or_times_revalidates_overlaps(): void
    {
        $this->process(['operator_id' => 'one', 'starts_at' => '2026-10-16 09:00', 'ends_at' => '2026-10-16 10:00']);
        $other = $this->process(['operator_id' => 'two', 'starts_at' => '2026-10-16 09:00', 'ends_at' => '2026-10-16 10:00']);
        $this->assertInvalid('operator_id', fn () => ProcessUpdaterHelper::update($other, ['operator_id' => 'one']));
        $this->assertSame('two', $other->fresh()->operator_id);
        $other->operator_id = 'one';
        $this->assertInvalid('operator_id', fn () => $other->save());
    }

    public function test_tool_and_processable_overlap_checks_and_opt_out(): void
    {
        foreach(['toolable', 'processable'] as $morph)
        {
            $attributes = [$morph . '_type' => 'test', $morph . '_id' => 'resource', 'starts_at' => '2026-10-16 09:00', 'ends_at' => '2026-10-16 10:00'];
            $this->process($attributes);
            $this->assertInvalid($morph . '_id', fn () => $this->process($attributes));
        }
        $this->type->update(['allows_overlaps' => true]);
        $this->assertNotNull($this->process($attributes)->id);
    }

    public function test_circular_parents_are_rejected_on_model_and_helper_updates(): void
    {
        $root = $this->process();
        $child = $this->process(['parent_id' => $root->id]);
        $this->assertInvalid('parent_id', fn () => ProcessUpdaterHelper::update($root, ['parent_id' => $child->id]));
        $root->parent_id = $root->id;
        $this->assertInvalid('parent_id', fn () => $root->save());
        $this->assertNull($root->fresh()->parent_id);
    }

    public function test_lock_unlock_and_closure_audit_fields_are_consistent(): void
    {
        $process = ProcessCreatorHelper::create(['type' => 'test', 'is_locked' => true, 'closed_at' => '2026-10-16 12:00'], ['user' => 'actor']);
        $this->assertSame('actor', $process->locked_by_user_id);
        $this->assertSame('actor', $process->closed_by_user_id);
        $this->assertNotNull($process->locked_at);
        $this->assertInvalid('is_locked', fn () => ProcessUpdaterHelper::update($process, ['notes' => 'blocked']));
        $process = ProcessUpdaterHelper::update($process, ['is_locked' => false, 'closed_at' => null, 'notes' => 'unlocked']);
        $this->assertFalse($process->is_locked);
        $this->assertNull($process->locked_at);
        $this->assertNull($process->locked_by_user_id);
        $this->assertNull($process->closed_by_user_id);
        $this->assertSame('unlocked', $process->notes);
    }

    public function test_failed_status_change_rolls_back_other_changes_and_unlock(): void
    {
        $process = $this->process(['is_locked' => true]);
        $final = ProcessStatus::create(['name' => 'done', 'label' => 'Done', 'is_active' => true]);
        try {
            ProcessUpdaterHelper::update($process, ['is_locked' => false, 'notes' => 'invalid', 'process_status_id' => $final->id]);
            $this->fail('Transition should fail.');
        } catch (LogicException $exception) {
            $this->assertTrue($process->fresh()->is_locked);
            $this->assertNull($process->fresh()->notes);
            $this->assertSame($this->draft->id, $process->fresh()->process_status_id);
            $this->assertSame(1, DB::table('processes__process_status_logs')->count());
        }
    }

    public function test_closure_and_status_are_atomic_and_locked_closure_requires_force(): void
    {
        $process = $this->process();
        $final = ProcessStatus::create(['name' => 'done', 'label' => 'Done', 'is_active' => true]);
        try {
            ProcessCloserHelper::close($process, ['status' => $final]);
            $this->fail('Transition should fail.');
        } catch (LogicException $exception) {
            $this->assertNull($process->fresh()->closed_at);
        }
        $process->refresh();
        ProcessLockerHelper::lock($process);
        try {
            ProcessCloserHelper::close($process);
            $this->fail('Locked closure should fail.');
        } catch (LogicException $exception) {
            $this->assertNull($process->fresh()->closed_at);
        }
    }

    public function test_transition_reason_permission_and_successful_closure(): void
    {
        $process = $this->process(['starts_at' => '2026-10-16 09:00']);
        $final = ProcessStatus::create(['name' => 'done', 'label' => 'Done', 'is_active' => true]);
        $transition = ProcessStatusTransition::create(['from_status_id' => $this->draft->id, 'to_status_id' => $final->id, 'is_allowed' => true, 'requires_reason' => true, 'requires_permission' => 'finish']);
        foreach([[], ['reason' => '   '], ['reason' => 'Done']] as $options)
        {
            try {
                ProcessCloserHelper::close($process->fresh(), array_merge($options, ['status' => $final]));
                $this->fail('Reason or permission should be required.');
            } catch (LogicException $exception) {
                $this->assertNull($process->fresh()->closed_at);
            }
        }
        $transition->update(['requires_permission' => null]);
        $closed = ProcessCloserHelper::close($process->fresh(), ['status' => $final, 'reason' => 'Done', 'set_ends_at' => true, 'closed_at' => '2026-10-16 10:00', 'user' => 'actor']);
        $this->assertSame($final->id, $closed->process_status_id);
        $this->assertSame(3600, $closed->duration_seconds);
        $this->assertSame('actor', $closed->closed_by_user_id);
        $this->assertSame(2, DB::table('processes__process_status_logs')->count());
    }
    public function test_nested_status_overrides_are_restored_even_after_an_exception(): void
    {
        $process = $this->process();
        $next = ProcessStatus::create(['name' => 'next', 'label' => 'Next', 'is_active' => true]);
        try {
            Process::allowingStatusUpdate(function () use ($process, $next) {
                Process::allowingStatusUpdate(fn () => null);
                $process->process_status_id = $next->id;
                $process->save();
                throw new LogicException('Test failure');
            });
        } catch (LogicException $exception) {
            $this->assertSame('Test failure', $exception->getMessage());
        }
        $this->assertSame($next->id, $process->fresh()->process_status_id);
        $process->process_status_id = $this->draft->id;
        $this->expectException(LogicException::class);
        $process->save();
    }

    public function test_audit_logs_are_readable_but_cannot_be_deleted_directly(): void
    {
        $process = $this->process();
        $this->assertSame(1, $process->statusLogs()->count());
        $this->expectException(LogicException::class);
        $process->getLastStatusLog()->delete();
    }

    public function test_unlock_can_correct_requirements_changed_since_the_process_was_locked(): void
    {
        $process = $this->process(['is_locked' => true]);
        $this->type->update(['requires_operator' => true]);
        $updated = ProcessUpdaterHelper::update($process, ['is_locked' => false, 'operator_id' => 'operator']);
        $this->assertFalse($updated->is_locked);
        $this->assertSame('operator', $updated->operator_id);
    }

}
