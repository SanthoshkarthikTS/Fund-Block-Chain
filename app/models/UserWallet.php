<?php

    use Illuminate\Support\MessageBag;

    class UserWallet extends Eloquent {

        protected $table = 'user_wallet';
        protected $fillable = array('amount','bitcoin');
        public $timestamps = false;

    }

?>