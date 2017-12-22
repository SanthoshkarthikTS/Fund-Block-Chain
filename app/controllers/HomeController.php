<?php

class HomeController extends BaseController {

	private $bitcoinClient;

	public function __construct(Blocktrail $client) {
		$this->bitcoinClient = $client;
	}

	public function showDashboard()
	{
		//get the user's wallets and their balances
		$user = User::find(Auth::user()->id);
		$wallets = $user->wallets;
		//lets also add up the balances of all wallets
		$totalBalance = 0;
		$totalUncBalance = 0;
		$wallets->each(function($wallet) use(&$totalBalance, &$totalUncBalance){
			$wallet->getBalance();
			$totalBalance += $wallet->balance;
			$totalUncBalance += $wallet->unc_balance;
		});

		//get the user's transaction history (paginated)
		$user->transactions = $user->transactions()->with(array('wallet' => function($query){
			$query->select(['id', 'name']);
		}))->orderBy('tx_time', 'desc')->paginate(10);

		$data = array(
			'wallets' => $wallets,
			'transactions' => $user->transactions,
			'totalBalance' => $totalBalance,
			'totalUncBalance' => $totalUncBalance,
		);

		return View::make('dashboard.home')->with($data);
	}

	public function addBitCoin() {
		$coin = Input::get('coin');
        $uid = Auth::user()->id;
		$user = UserWallet::where('uid','=',$uid)->first();
		$total_amount = $user->amount + $coin; 
		$user_wallet = new UserWallet;
		
		UserWallet::where('uid', '=', $uid)->update(array('amount' =>  $total_amount));

		return Redirect::route('dashboard');
	}

	public function buyBitCoin() {		
		$amount_given = Input::get('buyCoin');
		$uid = Auth::user()->id;
		$user = UserWallet::where('uid','=',$uid)->first();
		$amount = $user->amount - $amount_given; 
		$user_wallet = new UserWallet;
		
		$client = new GuzzleHttp\Client();
		$shareDetails = file_get_contents("https://bitaps.com/api/ticker");
		$details = json_decode($shareDetails, true);

		$bitcoin_in_usd = $amount_given / $details['usd'] + $user->bitcoin;

		UserWallet::where('uid', '=', $uid)->update(array('amount' =>  $amount, 'bitcoin' => $bitcoin_in_usd));
		return Redirect::route('dashboard');
	}
}
