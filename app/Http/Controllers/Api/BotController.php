<?php

namespace App\Http\Controllers\Api;

use App\Conversations\HelpConversation;
use App\Conversations\LogConversation;
use App\Http\Controllers\Controller;
use App\Slack\ApiHelper;
use App\SlackUser;
use Carbon\Carbon;
use Frlnc\Slack\Core\Commander;
use Illuminate\Http\Request;
use Mpociot\SlackBot\SlackBot;
use Symfony\Component\HttpFoundation\ParameterBag;

class BotController extends Controller
{
    /** @var SlackBot bot */
    private $bot = null;

    /** @var Commander api */
    private $api = null;

    private $api_helper = null;

    public function __construct()
    {
        $this->bot = app('slackbot');
        $this->api = app('slack_api');
        $this->api->execute('users.setActive');
        $this->api_helper = new ApiHelper();
    }

    /**
     * @param Request $request
     * @return null
     */
    public function eventListener(Request $request)
    {
        $payload = json_decode($request->getContent());
        return $this->handlePayload($payload);
    }


    /**
     * @param ParameterBag $payload
     * @return mixed|null
     */
    private function handlePayload($payload)
    {
        $response = null;
        $type = object_get($payload, 'type', null);
        switch ($type) {
            case 'url_verification':
                $response = $this->handleUrlVerification($payload);
                break;
            case 'event_callback':
                $event = object_get($payload, 'event', null);
                $userid = object_get($event, 'user', null);
                $bot_id = object_get($event, 'bot_id', null);
                if ($userid && empty($bot_id)) {
                    $user = $this->api_helper->getUser($userid);
                    $this->logListeners($event, $user);
                    $this->helpListeners($event, $user);
                    $this->bot->listen();
                }
                break;
        }
        return $response;
    }

    private function helpListeners($event, $user)
    {
        $this->bot->hears(
            '^(?:show )?leaderboard$',
            function (SlackBot $bot) use ($event, $user) {
                $bot->startConversation(new HelpConversation($event, $user, 'leaderboard'));
            }
        );
    }

    private function logListeners($event, $user)
    {
        $this->bot->hears(
            '^{steps}(?: steps)? yesterday$',
            function (SlackBot $bot, $steps) use ($event, $user) {
                $bot->startConversation(new LogConversation($event, $user, $steps, 'yesterday'));
            },
            SlackBot::DIRECT_MESSAGE
        );
        $this->bot->hears(
            '^{steps}(?: steps)? today$',
            function (SlackBot $bot, $steps) use ($event, $user) {
                $bot->startConversation(new LogConversation($event, $user, $steps, 'today'));
            },
            SlackBot::DIRECT_MESSAGE
        );
        $this->bot->hears(
            '^{steps}(?: steps)? on {date}$',
            function (SlackBot $bot, $steps, $date) use ($event, $user) {
                $bot->startConversation(new LogConversation($event, $user, $steps, $date));
            },
            SlackBot::DIRECT_MESSAGE
        );
        $this->bot->hears(
            '^{steps}(?: steps)? this week$',
            function (SlackBot $bot, $steps) use ($event, $user) {
                $bot->startConversation(new LogConversation($event, $user, $steps, 'this week'));
            },
            SlackBot::DIRECT_MESSAGE
        );
    }


    /**
     * @param ParameterBag $payload
     * @return mixed
     */
    private function handleUrlVerification($payload)
    {
        return object_get($payload, 'challenge', null);
    }
}