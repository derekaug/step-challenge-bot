<?php

namespace App\Conversations;

use App\SlackUser;
use App\StepLog;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Mpociot\SlackBot\Answer;
use Mpociot\SlackBot\Conversation;
use NumberFormatter;

class LogConversation extends Conversation
{
    protected $user;
    protected $new_log;
    protected $number_formatter;
    protected $api;
    protected $begins_at;
    protected $ends_at;

    public function __construct($event, SlackUser $user, $steps, $begins_at, $ends_at = null)
    {
        $this->event = $event;
        $this->new_log = new StepLog();
        $this->number_formatter = $this->getNumberFormatter();
        $this->user = $user;
        $this->api = app('slack_api');
        $this->begins_at = $begins_at;
        $this->ends_at = $ends_at;

        $this->new_log->steps = $this->number_formatter->parse($steps, NumberFormatter::TYPE_INT32);
        if ($begins_at === 'this week') {
            $this->new_log->begins_at = $this->getBeginThisWeek();
            $this->new_log->ends_at = $this->getEndThisWeek();
        } else {
            $begins_at = $this->isValidDate($begins_at) ? new Carbon($begins_at, $this->getTimezone()) : null;
            $ends_at = $this->isValidDate($ends_at) ? new Carbon($ends_at, $this->getTimezone()) : null;
            $this->new_log->begins_at = $begins_at;
            $this->new_log->ends_at = is_null($ends_at) ? $begins_at : $ends_at;
        }
    }

    protected function getTimezone()
    {
        return object_get($this->user, 'timezone', 'UTC');
    }

    protected function getLocal(Carbon $date)
    {
        return (new Carbon($date->toDateTimeString(), $this->getTimezone()));
    }

    protected function getLastFriday()
    {
        return (new Carbon('last friday', $this->getTimezone()))->setTime(0, 0, 0);
    }

    protected function getBeginThisWeek()
    {
        $begin = $this->getLastFriday();
        if (env('CHALLENGE_START', false)) {
            $start = new Carbon(env('CHALLENGE_START', $this->getTimezone()));
            $begin = $begin->lt($start) ? $start : $begin;
        }
        return $begin;
    }

    protected function getEndThisWeek()
    {
        $end = $this->getLastFriday()->addDays(7);
        if (env('CHALLENGE_FINISH', false)) {
            $finish = new Carbon(env('CHALLENGE_FINISH', $this->getTimezone()));
            $end = $end->gt($finish) ? $finish : $end;
        }
        return $end;
    }

    protected function logSteps()
    {
        $errors = $this->validate();
        if ($errors->count() <= 0) {
            $existing_logs = $this->user
                ->stepLogs()
                ->inRange($this->new_log->begins_at, $this->new_log->ends_at)
                ->get();

            if ($existing_logs->count() > 0) {
                $this->confirmOverwrite($existing_logs);
            } else {
                $this->saveLog();
            }
        } else {
            $this->displayErrors($errors);
        }
    }

    /**
     * @param Collection $existing_logs
     */
    protected function confirmOverwrite(Collection $existing_logs)
    {
        $count = $existing_logs->count();

        $ask = "Previous " . ($count === 1 ? "log" : "logs") . " found, would you like to replace them with new data:";
        foreach ($existing_logs as $log) {
            $ask .= "\n • " . $log;
        }
        $ask .= "\nRespond with *\"yes\"* to replace old data, or anything else to keep old data.";

        $this->ask($ask, function (Answer $answer) {
            if ($answer->getText() === 'yes') {
                DB::transaction(function() {
                    $this->user
                        ->stepLogs()
                        ->inRange($this->new_log->begins_at, $this->new_log->ends_at)
                        ->delete();

                    $this->saveLog();
                });
            }
        });
    }

    protected function displayErrors($errors)
    {
        $say = 'Failed to log your steps for the following reasons:';
        foreach ($errors as $error) {
            $say .= "\n • " . $error;
        }
        $this->say($say);
    }

    protected function saveLog()
    {
        $saved = $this->user->stepLogs()->save($this->new_log);
        if ($saved) {
            $this->say('Log saved: ' . $this->new_log);
        } else {
            $this->displayErrors(['There was a server error, please try again.']);
        }
    }

    protected function getTomorrow()
    {
        return Carbon::now($this->getTimezone())->setTime(0, 0, 0)->addDay(1);
    }

    protected function validate()
    {
        $rvalue = collect([]);

        if ($this->new_log->begins_at !== null && $this->new_log->type === StepLog::TYPE_DAY) {
            $begins = $this->getLocal($this->new_log->begins_at);
            if (
                $begins->lt($this->getBeginThisWeek()) ||
                $begins->gt($this->getEndThisWeek())
            ) {
                $rvalue->push('You can only log steps for dates in the current week.');
            }

            if ($begins->gte($this->getTomorrow())) {
                $rvalue->push('Steps can not be logged for future dates.');
            }
        }

        if ($this->begins_at !== null && !$this->isValidDate($this->begins_at)) {
            $rvalue->push('Invalid date string: ' . $this->begins_at);
        }

        if ($this->ends_at !== null && !$this->isValidDate($this->ends_at)) {
            $rvalue->push('Invalid date string: ' . $this->ends_at);
        }

        if ($this->new_log->steps <= 0) {
            $rvalue->push('You must log at least 1 step.');
        }

        return $rvalue;
    }

    protected function isValidDate($string)
    {
        return (bool)strtotime($string);
    }

    protected function getNumberFormatter()
    {
        return new NumberFormatter("en_EN", NumberFormatter::DECIMAL);
    }

    /**
     * @return mixed
     */
    public function run()
    {
        $this->logSteps();
    }
}