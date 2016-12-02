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
 * @property-read mixed $date_string
 * @property-read mixed $steps_display
 * @property-read mixed $date_display
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

    protected $appends = [
        'date_display',
        'steps_display'
    ];

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

    public function getDateDisplayAttribute()
    {
        $rvalue = '';
        switch ($this->type) {
            case static::TYPE_RANGE:
                $rvalue = $this->begins_at->toDateString() . ' to ' . $this->ends_at->toDateString();
                break;
            case static::TYPE_DAY:
                $rvalue = $this->begins_at->toDateString();
                break;
        }
        return $rvalue;
    }

    public function getStepsDisplayAttribute()
    {
        return number_format($this->steps) . ' ' . ($this->steps == 1 ? 'step' : 'steps');
    }

    public function __toString()
    {
        $rvalue = $this->steps_display;
        switch ($this->type) {
            case static::TYPE_RANGE:
                $rvalue .= 'from ' . $this->date_display;
                break;
            case static::TYPE_DAY:
                $rvalue .= 'on ' . $this->date_display;
                break;
        }
        return $rvalue;
    }
}