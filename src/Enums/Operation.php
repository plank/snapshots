<?php

namespace Plank\Snapshots\Enums;

enum Operation: string
{
    case Created = 'Created';
    case Snapshotted = 'Snapshotted';
    case Updated = 'Updated';
    case Deleted = 'Deleted';
    case Restored = 'Restored';
    case SoftDeleted = 'Hidden';
}
