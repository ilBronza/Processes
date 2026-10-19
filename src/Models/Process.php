<?php

namespace IlBronza\Processes\Models;

use Carbon\Carbon;
use IlBronza\Processes\Helpers\ProcessDurationCalculatorHelper;
use IlBronza\Processes\Helpers\ProcessValidatorHelper;
use IlBronza\Processes\Traits\HasProcessStatusLogs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use LogicException;

class Process extends ProcessPackageBaseModel
{
    use HasProcessStatusLogs;
    use SoftDeletes;

    public static $modelConfigPrefix = 'process';

    protected static bool $allowsDirectStatusUpdate = false;
    protected static bool $allowsLockedUpdate = false;

    protected $guarded = [];

    protected $casts = [
        'planned_starts_at' => 'datetime',
        'planned_ends_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'closed_at' => 'datetime',
        'locked_at' => 'datetime',
        'planned_duration_seconds' => 'integer',
        'duration_seconds' => 'integer',
        'sort_index' => 'integer',
        'priority' => 'integer',
        'is_locked' => 'boolean',
        'external_payload' => 'array',
        'data' => 'array',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $process) {
            ProcessValidatorHelper::validate($process);
            ProcessDurationCalculatorHelper::fillDurations($process);
        });

        static::updating(function (self $process) {
            if(config('processes.behavior.prevent_direct_status_update', true))
                if($process->isDirty('process_status_id') && ! static::$allowsDirectStatusUpdate)
                    throw new LogicException('Use ProcessStatusSetterHelper to change process status.');

            if(config('processes.behavior.prevent_locked_update', true))
                if($process->getOriginal('is_locked') && ! static::$allowsLockedUpdate)
                    throw new LogicException('Locked processes cannot be updated without force mode.');
        });
    }

    public static function allowingStatusUpdate(callable $callback): mixed
    {
        $previous = static::$allowsDirectStatusUpdate;
        static::$allowsDirectStatusUpdate = true;

        try
        {
            return $callback();
        }
        finally
        {
            static::$allowsDirectStatusUpdate = $previous;
        }
    }

    public static function allowingLockedUpdate(callable $callback): mixed
    {
        $previous = static::$allowsLockedUpdate;
        static::$allowsLockedUpdate = true;

        try
        {
            return $callback();
        }
        finally
        {
            static::$allowsLockedUpdate = $previous;
        }
    }

    public function user()
    {
        return $this->belongsTo(config('processes.models.user.class'));
    }

    public function operator()
    {
        return $this->belongsTo(config('processes.models.operator.class'));
    }

    public function processable()
    {
        return $this->morphTo();
    }

    public function toolable()
    {
        return $this->morphTo();
    }

    public function parent()
    {
        return $this->belongsTo(static::getProjectClassName(), 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(static::getProjectClassName(), 'parent_id');
    }

    public function type()
    {
        return $this->belongsTo(ProcessType::getProjectClassName(), 'process_type_id');
    }

    public function status()
    {
        return $this->belongsTo(ProcessStatus::getProjectClassName(), 'process_status_id');
    }

    public function closedByUser()
    {
        return $this->belongsTo(config('processes.models.user.class'), 'closed_by_user_id');
    }

    public function lockedByUser()
    {
        return $this->belongsTo(config('processes.models.user.class'), 'locked_by_user_id');
    }

    public function scopeActive(Builder $query): void
    {
        $query->whereNull('closed_at');
    }

    public function scopeClosed(Builder $query): void
    {
        $query->whereNotNull('closed_at');
    }

    public function scopeLocked(Builder $query): void
    {
        $query->where('is_locked', true);
    }

    public function scopeUnlocked(Builder $query): void
    {
        $query->where('is_locked', false);
    }

    public function scopeForUser(Builder $query, Model|string $user): void
    {
        $query->where('user_id', $user instanceof Model ? $user->getKey() : $user);
    }

    public function scopeForOperator(Builder $query, Model|string $operator): void
    {
        $query->where('operator_id', $operator instanceof Model ? $operator->getKey() : $operator);
    }

    public function scopeForType(Builder $query, ProcessType|string $type): void
    {
        $query->where('process_type_id', $type instanceof ProcessType ? $type->getKey() : $type);
    }

    public function scopeForStatus(Builder $query, ProcessStatus|string $status): void
    {
        $query->where('process_status_id', $status instanceof ProcessStatus ? $status->getKey() : $status);
    }

    public function scopeForProcessable(Builder $query, Model $processable): void
    {
        $query->where('processable_type', $processable->getMorphClass())
            ->where('processable_id', $processable->getKey());
    }

    public function scopeForTool(Builder $query, Model $tool): void
    {
        $query->where('toolable_type', $tool->getMorphClass())
            ->where('toolable_id', $tool->getKey());
    }

    public function scopeBetween(Builder $query, mixed $startsAt, mixed $endsAt): void
    {
        $query->where('starts_at', '>=', Carbon::parse($startsAt))
            ->where('ends_at', '<=', Carbon::parse($endsAt));
    }

    public function scopePlannedBetween(Builder $query, mixed $startsAt, mixed $endsAt): void
    {
        $query->where('planned_starts_at', '>=', Carbon::parse($startsAt))
            ->where('planned_ends_at', '<=', Carbon::parse($endsAt));
    }

    public function scopeOverlapping(Builder $query, mixed $startsAt, mixed $endsAt, string $prefix = ''): void
    {
        if(! in_array($prefix, ['', 'planned_'], true))
            throw new \InvalidArgumentException('Invalid process schedule.');

        $start = $prefix . 'starts_at';
        $end = $prefix . 'ends_at';
        $query->whereNotNull($start)
            ->where(function (Builder $query) use ($start, $end) {
                $query->whereNull($end)->orWhereColumn($end, '>', $start);
            });

        if($endsAt !== null)
        {
            if(Carbon::parse($endsAt)->lessThanOrEqualTo(Carbon::parse($startsAt)))
            {
                $query->whereRaw('1 = 0');
                return;
            }
            $query->where($start, '<', Carbon::parse($endsAt));
        }

        $query->where(function (Builder $query) use ($end, $startsAt) {
            $query->whereNull($end)->orWhere($end, '>', Carbon::parse($startsAt));
        });
    }

    public function scopeRoots(Builder $query): void
    {
        $query->whereNull('parent_id');
    }

    public function scopeChildrenOf(Builder $query, self|string $parent): void
    {
        $query->where('parent_id', $parent instanceof self ? $parent->getKey() : $parent);
    }

    public function scopeWithExternalReference(Builder $query, string $source, string $id): void
    {
        $query->where('external_source', $source)->where('external_id', $id);
    }
}
