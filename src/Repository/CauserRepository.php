<?php

namespace Plank\Snapshots\Repository;

use Illuminate\Support\Facades\Auth;
use Plank\Snapshots\Contracts\CausesChanges;
use Plank\Snapshots\Contracts\ResolvesCauser;
use Plank\Snapshots\Exceptions\CauserException;

class CauserRepository implements ResolvesCauser
{
    /**
     * Get an instance of the Model being used for versions.
     */
    public function active(): ?CausesChanges
    {
        /** @var CausesChanges $user */
        $user = Auth::user();

        if ($user && ! $user instanceof CausesChanges) {
            throw CauserException::create();
        }

        return $user;
    }
}
