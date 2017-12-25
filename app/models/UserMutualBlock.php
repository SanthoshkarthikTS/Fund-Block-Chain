<?php

use Illuminate\Support\MessageBag;

class UserMutualBlock extends Eloquent {

    protected $table = 'userMutualBlock';
    
	protected $fillable = array(
		'index',
		'uid',
		'sid',
		'amount',
	);

}
