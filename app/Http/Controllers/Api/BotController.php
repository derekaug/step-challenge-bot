<?php

namespace App\Http\Controllers\Api;

use App\Conversations\LogConversation;
use App\Http\Controllers\Controller;
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

    public function __construct()
    {
        $this->bot = app('slackbot');
        $this->api = app('slack_api');
        $this->api->execute('users.setActive');
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
                if ($userid) {
                    $user = $this->getUser($userid);
                    $this->bot->hears(
                        '{steps}( steps)? yesterday',
                        function (SlackBot $bot, $steps) use ($user) {
                            $bot->startConversation(new LogConversation($user, $steps, 'yesterday'));
                        },
                        SlackBot::DIRECT_MESSAGE
                    );
                    $this->bot->hears(
                        '{steps}( steps)? today',
                        function (SlackBot $bot, $steps) use ($user) {
                            $bot->startConversation(new LogConversation($user, $steps, 'today'));
                        },
                        SlackBot::DIRECT_MESSAGE
                    );
                    $this->bot->hears(
                        '{steps} on {date}',
                        function (SlackBot $bot, $steps, $date) use ($user) {
                            $bot->startConversation(new LogConversation($user, $steps, $date));
                        },
                        SlackBot::DIRECT_MESSAGE
                    );
                    $this->bot->hears(
                        '{steps} this week',
                        function (SlackBot $bot, $steps) use ($user) {
                            $bot->startConversation(new LogConversation($user, $steps, 'this week'));
                        },
                        SlackBot::DIRECT_MESSAGE
                    );
                    $this->bot->hears(
                        'now',
                        function (SlackBot $bot) use ($user) {
                            $bot->reply(Carbon::now(object_get($user, 'timezone', 'UTC'))->toDateTimeString());
                        },
                        SlackBot::DIRECT_MESSAGE
                    );
                    $this->bot->listen();
                }
                break;
        }
        return $response;
    }

    private function getUser($userid)
    {
        $response = $this->api->execute('users.info', ['user' => $userid])->getBody();
        $slack_user = SlackUser::firstOrNew([
            'slack_id' => array_get($response, 'user.id')
        ]);
        $slack_user->fill([
            'slack_id' => array_get($response, 'user.id'),
            'name' => array_get($response, 'user.name'),
            'first_name' => array_get($response, 'user.profile.first_name'),
            'last_name' => array_get($response, 'user.profile.last_name'),
            'email' => array_get($response, 'user.profile.email'),
            'image_avatar' => array_get($response, 'user.profile.image_192'),
            'image_original' => array_get($response, 'user.profile.image_original'),
            'timezone' => array_get($response, 'user.tz', 'America/Los_Angeles')
        ]);
        $slack_user->save();
        return $slack_user;
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