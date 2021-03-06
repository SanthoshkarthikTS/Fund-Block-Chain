<?php

use Blocktrail\SDK\BackupGenerator;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

/*---Block Explorer---*/
Route::group(['prefix' => 'explorer'], function($router) {
    Route::get('/', array('as' => 'explorer', 'uses' => 'ExplorerController@showHome'));
    Route::get('/search', array('as' => 'search', 'uses' => 'ExplorerController@search'));
    Route::get('/address/{address}', array('as' => 'address', 'uses' => 'ExplorerController@showAddress'));
    Route::get('/block/{block}', array('as' => 'block', 'uses' => 'ExplorerController@showBlock'));
    Route::get('/transaction/{transaction}', array('as' => 'transaction', 'uses' => 'ExplorerController@showTransaction'));
});

/*---Authentication---*/
Route::get('/login', array('as' => 'login', 'uses' => 'AuthController@showLogin'));
Route::post('/login', array('as' => 'login', 'uses' => 'AuthController@authenticate'));
Route::get('/logout', array('as' => 'logout', 'uses' => 'AuthController@logout'));

/*-- Dashboard Section --*/
Route::group(['before' => 'auth'], function($router){
    //Model bindings
    Route::model('wallet', 'Wallet');

    Route::get('/', array('as' => 'dashboard', 'uses' => 'HomeController@showDashboard'));

    //wallet routes
    Route::get('/wallet/new', array('as' => 'wallet.create', 'uses' => 'WalletController@showNewWallet'));
    Route::post('/wallet/new', array('as' => 'wallet.create', 'uses' => 'WalletController@createNewWallet'));
    Route::get('/wallet/{wallet}', array('as' => 'wallet.edit', 'uses' => 'WalletController@showWallet'));
    Route::post('/wallet/{wallet}', array('as' => 'wallet.edit', 'uses' => 'WalletController@updateWallet'));
    Route::get('/wallet/{wallet}/send', array('as' => 'wallet.send', 'uses' => 'WalletController@showSendPayment'));
    Route::post('/wallet/{wallet}/send', array('as' => 'wallet.send', 'uses' => 'WalletController@sendPayment'));
    Route::get('/wallet/{wallet}/confirm-payment', array('as' => 'wallet.confirm-send', 'uses' => 'WalletController@sendPayment'));
    Route::post('/wallet/{wallet}/confirm-payment', array('as' => 'wallet.confirm-send', 'uses' => 'WalletController@confirmPayment'));
    Route::get('/wallet/{wallet}/payment-result', array('as' => 'wallet.payment-result', 'uses' => 'WalletController@showPaymentResult'));
    Route::get('/wallet/{wallet}/receive', array('as' => 'wallet.receive', 'uses' => 'WalletController@showReceivePayment'));
    Route::post('/wallet/{wallet}/send-request', array('as' => 'wallet.send-request', 'uses' => 'WalletController@sendPaymentRequest'));
});

/*---Webhooks---*/
Route::group(['before' => 'auth.oncebasic'], function($router){
    Route::post('/webhook/{wallet_identity}', array('as' => 'webhook', 'uses' => 'WebhookController@webhookCalled'));
});

Route::post('/amount', array('as' => 'dashboard', 'uses' => 'HomeController@addBitCoin'));

Route::post('/', array('as' => 'dashboard', 'uses' => 'HomeController@buyBitCoin'));

Route::get('/history', array('as' => 'history', 'uses' => 'ExplorerController@history'));

Route::post('/investBTC', array('uses' => 'ExplorerController@investBitCoin'));

Route::post('/withdrawBTC', array('uses' => 'ExplorerController@withdrawBitCoin'));

Route::post('scheme', array('as' => 'scheme', 'uses' => 'ExplorerController@scheme'));

Route::post('withdraw', 
['as' => 'withdraw', 'uses' => 'ExplorerController@store']);

Route::post('invest', 
['as' => 'invest', 'uses' => 'ExplorerController@storeInvest']);

Route::get('api/user-investment', 'UserController@getAllInvestment');

Route::post('webhook-test', function() {
    //webhooks testing return whatever payload is sent
    $request = Request::instance();
    $input = $request->getContent();

    if (!$input ) {
        //if no Raw input sent, check for input sent by traditional "form-data" method
        $input = Input::all();
    }

    return $input;
});



/**
 * Testing route, for general testing and experimenting
 */
Route::get('test', function(){



    //create backup doc from wallet info
    $wallet = Wallet::first();
    $walletBackupGenerator = new BackupGenerator($wallet->primary_mnemonic, $wallet->backup_mnemonic, $wallet->blocktrail_keys);
    return $walletBackupGenerator->generateHTML();

});