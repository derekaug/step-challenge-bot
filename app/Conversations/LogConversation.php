<?php

namespace App\Conversations;

use App\SlackUser;
use Carbon\Carbon;
use Mpociot\SlackBot\Answer;
use Mpociot\SlackBot\Conversation;
use NumberFormatter;

class LogConversation extends Conversation
{
    protected $user;
    protected $steps;
    protected $begins_at;
    protected $ends_at;

    protected $number_formatter;

    public function __construct(SlackUser $user, $steps, $begins_at, $ends_at = null)
    {
        $this->number_formatter = $this->getNumberFormatter();
        $this->user = $user;
        $this->steps = $this->number_formatter->parse($steps, NumberFormatter::TYPE_INT32);
        if ($begins_at === 'this week') {
            $this->begins_at = $this->getBeginThisWeek();
            $this->ends_at = $this->getEndThisWeek();
        } else {
            $this->begins_at = new Carbon($begins_at, $this->getTimezone());
            $this->ends_at = is_null($ends_at) ? $this->begins_at : new Carbon($ends_at, $this->getTimezone());
        }
    }

    protected function getTimezone()
    {
        return object_get($this->user, 'timezone', 'UTC');
    }

    protected function getBeginThisWeek()
    {
        return new Carbon('last monday', $this->getTimezone());
    }

    protected function getEndThisWeek()
    {
        return $this->getBeginThisWeek()->addDays(7);
    }

    protected function logSteps()
    {
        if ($this->validate()) {
            $existing_logs = $this->user
                ->stepLogs()
                ->where('begins_at', '<=', $this->ends_at)
                ->where('ends_at', '>=', $this->begins_at)
                ->get();

            if ($existing_logs->count() > 0) {
                $this->confirmOverwrite();
            } else {
                $this->saveLog();
            }
        }
    }

    protected function confirmOverwrite()
    {
        $this->ask('Do you want to overwrite previous logs for this date?', function (Answer $answer) {
            if ($answer->getText() === 'yes') {
                $this->user
                    ->stepLogs()
                    ->where('begins_at', '<=', $this->ends_at)
                    ->where('ends_at', '>=', $this->begins_at)
                    ->delete();

                $this->saveLog();
            }
        });
    }

    protected function saveLog()
    {
        $saved = $this->user->stepLogs()->create([
            'steps' => $this->steps,
            'begins_at' => $this->begins_at,
            'ends_at' => $this->ends_at
        ]);
        if ($saved) {
            $this->say('Log saved (' . $this->begins_at->toDateTimeString() . ').');
        } else {
            $this->say('Log failed (' . $this->begins_at->toDateTimeString() . ').');
        }
    }

    protected function validate()
    {
        return true;
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