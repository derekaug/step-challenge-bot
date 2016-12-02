<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\SlackUser
 *
 * @property integer $id
 * @property string $slack_id
 * @property string $name
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $image_avatar
 * @property string $image_original
 * @property string $timezone
 * @property integer $steps
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read mixed $full_name
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\StepLog[] $stepLogs
 * @method static \Illuminate\Database\Query\Builder|\App\SlackUser whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SlackUser whereSlackId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SlackUser whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SlackUser whereFirstName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SlackUser whereLastName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SlackUser whereEmail($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SlackUser whereImageAvatar($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SlackUser whereImageOriginal($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SlackUser whereTimezone($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SlackUser whereSteps($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SlackUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SlackUser whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Query\Builder|\App\SlackUser leaderboard()
 * @property-read mixed $formatted_steps
 * @property-read mixed $steps_display
 */
class SlackUser extends Eloquent
{
    protected $fillable = [
        'slack_id',
        'name',
        'first_name',
        'last_name',
        'email',
        'image_avatar',
        'image_original',
        'timezone',
        'steps'
    ];

    protected $appends = [
        'full_name',
        'steps_display'
    ];

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getStepsDisplayAttribute()
    {
        return number_format($this->steps) . ' ' . ($this->steps == 1 ? 'step' : 'steps');
    }

    public function stepLogs()
    {
        return $this->hasMany(StepLog::class);
    }

    public function scopeLeaderboard(Builder $query)
    {
        return $query
            ->orderBy('steps', 'desc')
            ->orderBy('first_name', 'asc')
            ->orderBy('last_name', 'asc');
    }
}