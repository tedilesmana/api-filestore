<?php

namespace App\Repositories\Interfaces\Auth;

interface AuthRepositoryInterface
{
    public function register($request);
    public function login($request);
    public function loginWithWhatsApp($request);
    public function createToken($username, $password);
    public function createTokenWithWhatsApp($request);
}
