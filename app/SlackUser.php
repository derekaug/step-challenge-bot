<?php

namespace App;

use Eloquent;


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
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read mixed $full_name
 * @method static \Illuminate\Database\Query\Builder|\App\SlackUser whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SlackUser whereSlackId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SlackUser whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SlackUser whereFirstName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SlackUser whereLastName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SlackUser whereEmail($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SlackUser whereImageAvatar($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SlackUser whereImageOriginal($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SlackUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SlackUser whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\StepLog[] $stepLogs
 * @property string $timezone
 * @property integer $steps
 * @method static \Illuminate\Database\Query\Builder|\App\SlackUser whereTimezone($value)
 * @method static \Illuminate\Database\Query\Builder|\App\SlackUser whereSteps($value)
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
        'full_name'
    ];

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function stepLogs()
    {
        return $this->hasMany(StepLog::class);
    }
}