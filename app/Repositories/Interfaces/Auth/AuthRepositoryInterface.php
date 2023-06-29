<?php

namespace App\Repositories\Interfaces\Auth;

interface AuthRepositoryInterface
{
    public function listJabatan($request);
    public function getAllUser($request);
    public function register($request);
    public function login($request);
    public function loginWithWhatsApp($request);
    public function createToken($username, $password);
    public function createTokenWithWhatsApp($request);
}
