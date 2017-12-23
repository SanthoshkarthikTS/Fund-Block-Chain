<?php

use Illuminate\Support\MessageBag;

class TransactionHistory extends Eloquent {

    protected $table = 'transaction_history';
    protected $fillable = array('withdraw','invest','uid','mid','transaction_at');
    public $timestamps = false;


}

?>