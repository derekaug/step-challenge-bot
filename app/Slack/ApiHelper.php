<?php
/**
 * Created by PhpStorm.
 * User: derek.augustine
 * Date: 12/2/16
 * Time: 5:42 PM
 */

namespace App\Slack;

use App\SlackUser;
use DB;

class ApiHelper
{
    protected $api;

    public function __construct()
    {
        $this->api = app('slack_api');
    }

    public function getUser($user_id)
    {
        $slack_user = null;
        $response = $this->api->execute('users.info', ['user' => $user_id])->getBody();
        if (!array_get($response, 'user.is_bot')) {
            DB::transaction(function () use ($response, &$slack_user) {
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
            });
        }
        return $slack_user;
    }

    public function getChannelId($channel_name)
    {
        $response = $this->api->execute('channels.list')->getBody();
        $channels = collect(array_get($response, 'channels'));
        $channel = $channels->where('name', $channel_name)->first();
        return array_get($channel, 'id');
    }

    public function getUsersInChannel($channel_name)
    {
        $existing_ids = SlackUser::all(['slack_id'])->pluck('slack_id')->toArray();
        $channel_id = $this->getChannelId($channel_name);
        $response = $this->api->execute('channels.info', ['channel' => $channel_id])->getBody();
        $channel = array_get($response, 'channel');
        $members = array_get($channel, 'members');
        $new_members = array_diff($members, $existing_ids);
        foreach ($new_members as $member) {
            $this->getUser($member);
        }
    }
}