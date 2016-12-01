<?php

namespace App\Conversations;

use App\SlackUser;
use App\StepLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Mpociot\SlackBot\Answer;
use Mpociot\SlackBot\Conversation;
use NumberFormatter;

class LogConversation extends Conversation
{
    protected $user;
    protected $new_log;
    protected $number_formatter;

    public function __construct(SlackUser $user, $steps, $begins_at, $ends_at = null)
    {
        $this->new_log = new StepLog();
        $this->number_formatter = $this->getNumberFormatter();
        $this->user = $user;

        $this->new_log->steps = $this->number_formatter->parse($steps, NumberFormatter::TYPE_INT32);
        if ($begins_at === 'this week') {
            $this->new_log->begins_at = $this->getBeginThisWeek();
            $this->new_log->ends_at = $this->getEndThisWeek();
        } else {
            $this->new_log->begins_at = new Carbon($begins_at, $this->getTimezone());
            $this->new_log->ends_at = is_null($ends_at) ? $this->new_log->begins_at : new Carbon($ends_at, $this->getTimezone());
        }
    }

    protected function getTimezone()
    {
        return object_get($this->user, 'timezone', 'UTC');
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
        if ($this->validate()) {
            $existing_logs = $this->user
                ->stepLogs()
                ->inRange($this->new_log->begins_at, $this->new_log->ends_at)
                ->get();

            if ($existing_logs->count() > 0) {
                $this->confirmOverwrite($existing_logs);
            } else {
                $this->saveLog();
            }
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
                $this->user
                    ->stepLogs()
                    ->inRange($this->new_log->begins_at, $this->new_log->ends_at)
                    ->delete();

                $this->saveLog();
            }
        });
    }

    protected function saveLog()
    {
        $saved = $this->user->stepLogs()->save($this->new_log);
        if ($saved) {
            $this->say('Log saved: ' . $this->new_log;
        } else {
            $this->say('Log failed: ' . $this->new_log;
        }
    }

    protected function validate()
    {
        $rvalue = collect([]);

        if ($this->new_log->type === StepLog::TYPE_DAY) {
            if ($this->new_log->begins_at->lt($this->getBeginThisWeek())) {
                $rvalue->push('Only dates in the current week can be logged.');
            }
        } else {

        }

        return [];
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