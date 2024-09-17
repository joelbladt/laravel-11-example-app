<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Eloquent\LogMessageInterface;
use App\Models\LogMessage;
use App\Repositories\Repository as BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class LogMessageRepository extends BaseRepository implements LogMessageInterface
{
    public function __construct()
    {
        parent::__construct(new LogMessage());
    }

    public function all(): Collection
    {
        return $this->model->all();
    }
}
