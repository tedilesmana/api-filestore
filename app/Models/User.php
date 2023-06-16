<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function findForPassport($username)
    {
        return $this->where('username', $username)->first();
    }

    public function validateForPassportPasswordGrant($password)
    {
        if (Hash::check($password, $this->user_password)) {
            return Hash::check($password, $this->user_password);
        } else {
            return Hash::check($password, $this->google_password);
        }
    }

    public function AauthAcessToken()
    {
        return $this->hasMany(OauthAccessToken::class);
    }

    // public function userDetails()
    // {
    //     return $this->hasMany(UserDetail::class);
    // }

    public function userDetail()
    {
        return $this->hasOne(UserDetail::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function dlbEmployee()
    {
        return $this->hasOne(DlbEmployee::class);
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }
}
