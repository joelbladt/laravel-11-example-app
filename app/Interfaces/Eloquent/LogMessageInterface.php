<?php

namespace App\Interfaces\Eloquent;

use Illuminate\Database\Eloquent\Collection;

interface LogMessageInterface
{
    public function all(): Collection;
}
