<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property string $level_name
 * @property int $level
 * @property string $message
 * @property string|null $logged_at
 * @property \ArrayObject $context
 * @property \ArrayObject $extra
 * @method static \Database\Factories\LogMessageFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|LogMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LogMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LogMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder|LogMessage whereContext($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogMessage whereExtra($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogMessage whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogMessage whereLevelName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogMessage whereLoggedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogMessage whereMessage($value)
 * @mixin \Eloquent
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

    /**
     * @return string
     */
    public function getLevelName(): string
    {
        return $this->level_name;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    public function getLogged(): string
    {
        return $this->asDateTime($this->logged_at)->setTimezone(config('app.timezone'));
    }

    /**
     * @return \ArrayObject
     */
    public function getContext(): \ArrayObject
    {
        return $this->context;
    }

    /**
     * @return \ArrayObject
     */
    public function getExtra(): \ArrayObject
    {
        return $this->extra;
    }
}
