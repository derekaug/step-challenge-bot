<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function authSlack()
    {
        return Socialite::with('slack')
            ->scopes(['bot', 'users:read', 'users.profile:read'])
            ->redirect();
    }

    public function authSlackCallback(Client $httpClient)
    {
        $response = $httpClient->post('https://slack.com/api/oauth.access', [
            'headers' => ['Accept' => 'application/json'],
            'form_params' => [
                'client_id' => env('SLACK_KEY'),
                'client_secret' => env('SLACK_SECRET'),
                'code' => $_GET['code'],
                'redirect_uri' => env('SLACK_REDIRECT_URI'),
            ]
        ]);
        $bot_token = json_decode($response->getBody())->bot->bot_access_token;
        echo "Your Bot Token is: " . $bot_token . " place it inside your .env as SLACK_TOKEN";
    }
}
