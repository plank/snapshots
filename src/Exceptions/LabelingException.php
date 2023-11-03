<?php

namespace Plank\Snapshots\Exceptions;

class LabelingException extends SnapshotsException
{
    public static function create(string $model): self
    {
        return new self('Model `'.$model.'` must implement the `'.\Plank\Snapshots\Contracts\Versioned::class.'` to use the History Labler.');
    }
}
