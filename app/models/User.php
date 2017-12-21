<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends Eloquent implements UserInterface, RemindableInterface {

	use UserTrait, RemindableTrait;


	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $guarded = array();

	protected $hidden = array('password', 'remember_token');

	protected $hashableAttributes = array(
		'password',
	);

	public static $rules = array(
		'email'      => 'email|required',
		'password'   => 'required|min:3',
	);

	public function setPasswordAttribute($value)
	{
		$this->attributes['password'] = Hash::make($value);
	}


	public function wallets()
	{
		return $this->hasMany('Wallet');
	}

	public function transactions()
	{
		return $this->hasManyThrough('Transaction', 'Wallet');
	}

}
