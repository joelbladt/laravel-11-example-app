<?php

namespace App\Models;

use ArrayObject;
use Database\Factories\LogMessageFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $level_name
 * @property int $level
 * @property string $message
 * @property string|null $logged_at
 * @property ArrayObject $context
 * @property ArrayObject $extra
 *
 * @method static LogMessageFactory factory($count = null, $state = [])
 * @method static Builder|LogMessage newModelQuery()
 * @method static Builder|LogMessage newQuery()
 * @method static Builder|LogMessage query()
 * @method static Builder|LogMessage whereContext($value)
 * @method static Builder|LogMessage whereExtra($value)
 * @method static Builder|LogMessage whereId($value)
 * @method static Builder|LogMessage whereLevel($value)
 * @method static Builder|LogMessage whereLevelName($value)
 * @method static Builder|LogMessage whereLoggedAt($value)
 * @method static Builder|LogMessage whereMessage($value)
 *
 * @mixin Eloquent
 */
class LogMessage extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'logs';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'context' => AsArrayObject::class,
        'extra' => AsArrayObject::class,
    ];

    public function getContext(): ArrayObject
    {
        return $this->context;
    }

    public function getExtra(): ArrayObject
    {
        return $this->extra;
    }

    protected function LevelName(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => mb_strtoupper($value),
        );
    }
}
