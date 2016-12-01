<?php

namespace App;

use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\StepLog
 *
 * @property integer $slack_user_id
 * @property integer $steps
 * @property \Carbon\Carbon $begins_at
 * @property \Carbon\Carbon $ends_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\SlackUser $slackUser
 * @property-read mixed $type
 * @method static \Illuminate\Database\Query\Builder|\App\StepLog whereSlackUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\StepLog whereSteps($value)
 * @method static \Illuminate\Database\Query\Builder|\App\StepLog whereBeginsAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\StepLog whereEndsAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\StepLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\StepLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\StepLog inRange($begin, $end)
 * @mixin \Eloquent
 */
class StepLog extends Eloquent
{
    const TYPE_DAY = 'date';
    const TYPE_RANGE = 'range';

    protected $fillable = [
        'slack_user_id',
        'steps',
        'begins_at',
        'ends_at'
    ];

    protected $dates = [
        'begins_at',
        'ends_at'
    ];

    protected $appends = [];

    public function slackUser()
    {
        return $this->belongsTo(SlackUser::class);
    }

    public function getTypeAttribute()
    {
        return $this->begins_at->eq($this->ends_at) ? static::TYPE_DAY : static::TYPE_RANGE;
    }

    /**
     * @param Builder $query
     * @param Carbon $begin
     * @param Carbon $end
     * @return Builder
     */
    public function scopeInRange(Builder $query, Carbon $begin, Carbon $end)
    {
        return $query
            ->where('begins_at', '<=', $end)
            ->where('ends_at', '>=', $begin);
    }

    public function __toString()
    {
        $rvalue = number_format($this->steps) . ' steps ';
        switch ($this->type) {
            case static::TYPE_RANGE:
                $rvalue .= 'between ' . $this->begins_at->toDateString() . ' and ' . $this->ends_at->toDateString();
                break;
            case static::TYPE_DAY:
                $rvalue .= 'on ' .$this->begins_at->toDateString();
                break;
        }
        return $rvalue;
    }
}