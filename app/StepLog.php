<?php

namespace App;

use Eloquent;


/**
 * App\StepLog
 *
 * @property integer $slack_user_id
 * @property integer $steps
 * @property string $begins_at
 * @property string $ends_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\StepLog whereSlackUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\StepLog whereSteps($value)
 * @method static \Illuminate\Database\Query\Builder|\App\StepLog whereBeginsAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\StepLog whereEndsAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\StepLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\StepLog whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class StepLog extends Eloquent
{
    protected $fillable = [
        'slack_user_id',
        'steps',
        'begins_at',
        'ends_at'
    ];

    protected $appends = [];
}