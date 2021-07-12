<?php


namespace App\Services;


class UserServices
{
    public function getByUserName($username) {
        DB::table('users')->where('username', $username)->where('deleted', 0)->first();
    }
}
