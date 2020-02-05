<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable,HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token','email_verified_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public $rules = [
        'id' => 'required',
    ];
    
    const CRUD_rules = [
        'create' => [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required'
        ],
        'update' => [
            'id' => 'required|exists:users,id',
            'name' => 'required',
        ],
        'read' => [
            'id' => 'required|exists:users,id',
        ],
        'delete' => [
            'id' => 'required|exists:users,id',
        ]
    ];


    public static function getRules($method,$id = null){
        $rules = self::CRUD_rules[$method];
        if($method == 'update' && isset($id) ){
            $rules['email'] =  'required|email|unique:users,email,'.$id;
        }
        return $rules;
    }
    
    
}
