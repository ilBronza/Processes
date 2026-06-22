<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('processes.models.processStatus.table'), function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('label');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort_index')->nullable()->index();
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_initial')->default(false)->index();
            $table->boolean('is_final')->default(false)->index();
            $table->boolean('is_positive')->default(false)->index();
            $table->boolean('is_negative')->default(false)->index();
            $table->boolean('is_blocking')->default(false)->index();
            $table->json('data')->nullable();
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create(config('processes.models.processType.table'), function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('label');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort_index')->nullable()->index();
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->uuid('default_status_id')->nullable()->index();
            $table->boolean('allows_overlaps')->default(false);
            $table->boolean('requires_operator')->default(false);
            $table->boolean('requires_tool')->default(false);
            $table->boolean('requires_processable')->default(false);
            $table->json('data')->nullable();
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('default_status_id')->references('id')->on(config('processes.models.processStatus.table'))->nullOnDelete();
        });

        Schema::create(config('processes.models.process.table'), function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable()->index();
            $table->uuid('operator_id')->nullable()->index();
            $this->nullableMorphs($table, 'processable');
            $this->nullableMorphs($table, 'toolable');
            $table->uuid('parent_id')->nullable()->index();
            $table->uuid('process_type_id')->index();
            $table->uuid('process_status_id')->index();
            $table->dateTime('planned_starts_at')->nullable()->index();
            $table->dateTime('planned_ends_at')->nullable()->index();
            $table->dateTime('starts_at')->nullable()->index();
            $table->dateTime('ends_at')->nullable()->index();
            $table->unsignedBigInteger('planned_duration_seconds')->nullable();
            $table->unsignedBigInteger('duration_seconds')->nullable();
            $table->dateTime('closed_at')->nullable()->index();
            $table->uuid('closed_by_user_id')->nullable()->index();
            $table->boolean('is_locked')->default(false)->index();
            $table->dateTime('locked_at')->nullable();
            $table->uuid('locked_by_user_id')->nullable()->index();
            $table->integer('sort_index')->nullable()->index();
            $table->integer('priority')->nullable()->index();
            $table->string('external_id')->nullable();
            $table->string('external_source')->nullable();
            $table->json('external_payload')->nullable();
            $table->json('data')->nullable();
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on(config('processes.models.process.table'))->nullOnDelete();
            $table->foreign('process_type_id')->references('id')->on(config('processes.models.processType.table'))->onDelete('restrict');
            $table->foreign('process_status_id')->references('id')->on(config('processes.models.processStatus.table'))->onDelete('restrict');

            $table->index(['external_source', 'external_id'], 'processes_external_reference_idx');
            $table->index(['operator_id', 'starts_at', 'ends_at'], 'processes_operator_range_idx');
            $table->index(['toolable_type', 'toolable_id', 'starts_at', 'ends_at'], 'processes_toolable_range_idx');
            $table->index(['processable_type', 'processable_id', 'starts_at', 'ends_at'], 'processes_processable_range_idx');
        });

        Schema::create(config('processes.models.processStatusLog.table'), function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('process_id')->index();
            $table->uuid('process_status_id')->index();
            $table->uuid('previous_process_status_id')->nullable()->index();
            $table->uuid('user_id')->nullable()->index();
            $table->dateTime('set_at')->index();
            $table->text('reason')->nullable();
            $table->json('data')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('process_id')->references('id')->on(config('processes.models.process.table'))->cascadeOnDelete();
            $table->foreign('process_status_id')->references('id')->on(config('processes.models.processStatus.table'))->onDelete('restrict');
            $table->foreign('previous_process_status_id')->references('id')->on(config('processes.models.processStatus.table'))->nullOnDelete();
            $table->index(['process_id', 'set_at']);
        });

        Schema::create(config('processes.models.processStatusTransition.table'), function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('process_type_id')->nullable()->index();
            $table->uuid('from_status_id')->nullable()->index();
            $table->uuid('to_status_id')->index();
            $table->boolean('is_allowed')->default(true)->index();
            $table->boolean('requires_reason')->default(false);
            $table->string('requires_permission')->nullable();
            $table->integer('sort_index')->nullable()->index();
            $table->json('data')->nullable();
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('process_type_id')->references('id')->on(config('processes.models.processType.table'))->cascadeOnDelete();
            $table->foreign('from_status_id')->references('id')->on(config('processes.models.processStatus.table'))->cascadeOnDelete();
            $table->foreign('to_status_id')->references('id')->on(config('processes.models.processStatus.table'))->cascadeOnDelete();
        });

        Schema::create(config('processes.models.processTypeField.table'), function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('process_type_id')->index();
            $table->string('name');
            $table->string('label');
            $table->string('type');
            $table->boolean('is_required')->default(false);
            $table->text('default_value')->nullable();
            $table->integer('sort_index')->nullable()->index();
            $table->json('options')->nullable();
            $table->json('validation_rules')->nullable();
            $table->json('data')->nullable();
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('process_type_id')->references('id')->on(config('processes.models.processType.table'))->cascadeOnDelete();
            $table->unique(['process_type_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('processes.models.processTypeField.table'));
        Schema::dropIfExists(config('processes.models.processStatusTransition.table'));
        Schema::dropIfExists(config('processes.models.processStatusLog.table'));
        Schema::dropIfExists(config('processes.models.process.table'));
        Schema::dropIfExists(config('processes.models.processType.table'));
        Schema::dropIfExists(config('processes.models.processStatus.table'));
    }

    private function nullableMorphs(Blueprint $table, string $name): void
    {
        if(config('processes.behavior.morph_id_type', 'uuid') === 'uuid')
            $table->nullableUuidMorphs($name);
        else
            $table->nullableMorphs($name);
    }
};
