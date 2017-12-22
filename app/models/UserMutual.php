<?php

    use Illuminate\Support\MessageBag;

    class UserMutual extends Eloquent {

        protected $table = 'user_mutual_fund';
        protected $fillable = array('amount');
        public $timestamps = false;

    }

?>