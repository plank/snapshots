<?php

namespace Plank\Snapshots\Contracts;

interface CopiesTables
{
    public function copy(string $from, string $to): void;
}
