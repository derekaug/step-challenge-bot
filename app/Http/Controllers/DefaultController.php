<?php
/**
 * Created by PhpStorm.
 * User: derek.augustine
 * Date: 11/23/16
 * Time: 4:14 PM
 */

namespace App\Http\Controllers;


use App\SlackUser;

class DefaultController extends Controller
{
    public function getIndex()
    {
        $users = SlackUser::all();
        return view('index', ['users' => $users]);
    }
}