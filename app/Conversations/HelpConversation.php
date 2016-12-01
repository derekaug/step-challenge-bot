<?php

namespace App\Conversations;

use App\SlackUser;
use App\StepLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Mpociot\SlackBot\Answer;
use Mpociot\SlackBot\Conversation;
use NumberFormatter;

class HelpConversation extends Conversation
{
    protected $user;
    protected $api;
    protected $event;
    protected $command;

    public function __construct($event, SlackUser $user, $command)
    {
        $this->user = $user;
        $this->api = app('slack_api');
        $this->event = $event;
        $this->command = $command;

    }

    protected function runHelp()
    {
        switch ($this->command) {
            case 'leaderboard':
                $this->showLeaderboard();
                break;
        }
    }

    protected function showLeaderboard()
    {
        $top = SlackUser::leaderboard()->limit(10)->get();
        $count = $top->count();
        $say = "*_Top " . $count . ($count === 1 ? " Stepper" : " Steppers") . "_*";
        $i = 0;
        $rank = 1;
        $value = null;
        foreach ($top as $user) {
            if($value !== null){
                if($user->steps < $value){
                    $rank += $i;
                    $i = 0;
                }
            }
            ++$i;
            $value = $user->steps;
            $say .= "\n*" . $rank . ".* " . $user->full_name . " (@" . $user->name . ")";
            $say .= " - " . number_format($user->steps) . " steps";
        }
        $say .= "\n Full Leaderboard: " . route('index');
        $this->say($say, ['parse' => 'full']);

    }

    /**
     * @return mixed
     */
    public function run()
    {
        $this->runHelp();
    }
}