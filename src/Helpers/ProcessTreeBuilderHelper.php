<?php

namespace IlBronza\Processes\Helpers;

use IlBronza\Processes\Models\Process;
use LogicException;

class ProcessTreeBuilderHelper
{
    public static function assignParent(Process $process, ?Process $parent): Process
    {
        if(! $parent)
        {
            $process->parent_id = null;
            $process->save();

            return $process;
        }

        static::ensureValidParent($process, $parent);

        $process->parent_id = $parent->getKey();
        $process->save();

        return $process;
    }

    public static function roots()
    {
        return Process::getProjectClassName()::query()->roots()->with('children')->get();
    }

    public static function ensureValidParent(Process $process, Process $parent): void
    {
        if($process->getKey() === $parent->getKey())
            throw new LogicException('A process cannot be its own parent.');

        $current = $parent;
        $visited = [];

        while($current)
        {
            if(isset($visited[$current->getKey()]))
                throw new LogicException('Circular process hierarchies are not allowed.');
            $visited[$current->getKey()] = true;
            if($current->getKey() === $process->getKey())
                throw new LogicException('Circular process hierarchies are not allowed.');

            if($process->getKey() !== null && $current->parent_id === $process->getKey())
                throw new LogicException('Circular process hierarchies are not allowed.');

            $current = $current->parent()->first();
        }
    }
}
