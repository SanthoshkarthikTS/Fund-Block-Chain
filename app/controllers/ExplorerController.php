<?php

class ExplorerController extends BaseController {

    private $bitcoinClient;

    public function __construct(Blocktrail $client) {
        $this->bitcoinClient = $client;
    }

    public function store() {
        // $amount = Input::get('amount');
         $block = 1;
         $userFunds = UserMutual::findOrFail($block);
         // echo "<pre>";
         // print_r($userFunds); exit;
         $transaction_history = new TransactionHistory();
         // add other fields
         $previousAmount = $userFunds->amount;
         $withdraw = Input::get('amount');
         $balance = $previousAmount - $withdraw;
         
         if($balance>0) {
             $userFunds->amount = $balance;
             $transaction_history->withdraw= $withdraw;
             $transaction_history->transaction_at = date('Y-m-d H:i:s');
             $transaction_history->uid = $userFunds['uid'];
             $transaction_history->mid = $userFunds['mid'];
             $transaction_history->save();
             $userFunds->save();
         } else {
             // print_r("Minimum balance crossed");exit();
             $data = array(
                 "title" => "",
                 "subtitle" => "An Error Ocurred",
                 "message" => "Minimum balance crossed",
             );
             return View::make('error.general', $data);
         }
         
     
         return Redirect::route('explorer');
     
         //return "true";  
     }
     
     public function storeInvest() {
        // $amount = Input::get('amount');
         $block = 1;
         $userFunds = UserMutual::findOrFail($block);
         //$transaction_history = new TransactionHistory();
         $previousAmount = $userFunds->amount;
         $invest = Input::get('amount');
         $balance = $previousAmount + $invest;
         $userFunds->amount = $balance;
         $transaction_history->invest= $invest;
         $transaction_history->transaction_at = date('Y-m-d H:i:s');
         $transaction_history->uid = $userFunds['uid'];
         $transaction_history->mid = $userFunds['mid'];
         $transaction_history->save();
         $userFunds->save();
         return Redirect::route('explorer');
     
         //return "true";  
     }     

    public function showHome()
    {
        try {
            $uid = Auth::user()->id;   
            
            //get the latest few blocks
            $sqlQuery = "SELECT * FROM `schemes` WHERE id NOT IN (SELECT DISTINCT(mid) FROM `user_mutual_fund` WHERE uid = $uid)";
            $filterSchemeDetails = DB::select(DB::raw($sqlQuery));

            $schemeDetails = json_decode( json_encode($filterSchemeDetails), true);
            
            $blocks = $this->bitcoinClient->allBlocks($page = 1, $limit = 5, $sortDir = 'desc');

            $userFunds = UserMutual::where('uid', '=', $uid)->get();

            $select = DB::table('user_mutual_fund')->select('mid')->where('uid', '=', $uid)->get();

            $finalMid = json_decode( json_encode($select), true);

            //create the view, passing the data
            $data = array('fundId' => $finalMid, 'blocks' => $blocks, 'userFunds' => $userFunds, 'schemeDetails' => $schemeDetails);
            return View::make('explorer.home', $data);
            

        } catch(Exception $e) {
            $data = array(
                "title"    => "",
                "subtitle" => "An Error Ocurred",
                "message" => $e->getMessage(),
            );
            return View::make('error.general', $data);
        }
    }

    public function showAddress($address)
    {
        try {
            //get the address data
            $addressInfo = $this->bitcoinClient->address($address);
            //get the address transactions
            $page = Input::get('page', 1);
            $transactions = $this->bitcoinClient->addressTransactions($address, $page, $limit=20, $sortDir='desc');

            //create an instance of the Paginator for easy pagination of the results
            $transactions = Paginator::make($transactions['data'], $transactions['total'], $transactions['per_page']);
            
            $data = array('summary' => $addressInfo, 'transactions' => $transactions);
            return View::make('explorer.address', $data);

        } catch(Exception $e) {
            $data = array(
                "title"    => "Bitcoin Address",
                "subtitle" => "Could Not Get Address Data",
                "message" => $e->getMessage(),
            );
            return View::make('error.general', $data);
        }
    }

    public function showTransaction($txhash)
    {
        try {
            //get the transaction data
            $data = $this->bitcoinClient->transaction($txhash);

            return View::make('explorer.transaction', $data);

        } catch(Exception $e) {
            $data = array(
                "title"    => "Bitcoin Transaction",
                "subtitle" => "Could Not Get Transaction Data",
                "message" => $e->getMessage(),
            );
            return View::make('error.general', $data);
        }
    }

    public function showBlock($block)
    {
       
        try {    
            $uid = Auth::user()->id;     
            $userFunds = DB::table('user_mutual_fund')->where('uid', $uid)->where('mid', $block)->get();

            $schemes = DB::table('schemes')->where('id', $block)->get();
            $api = $schemes[0]->api;

            $client = new GuzzleHttp\Client();            
            $shareDetails = file_get_contents($api);   
            $details = json_decode($shareDetails, true);

            $users = DB::table('user_logs')->get();
            //get the block data
            $blockInfo = $this->bitcoinClient->block($block);
            //get the block transactions
            $page = Input::get('page', 1);
            $transactions = $this->bitcoinClient->blockTransactions($block, $page, $limit=20, $sortDir='desc');
            
            //create an instance of the Paginator for easy pagination of the results
            $transactions = Paginator::make($transactions['data'], $transactions['total'], $transactions['per_page']);
            
            //Wallet Amount
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
    
            $walletDetails = array(
                'totalUncBalance' => $totalBalance,
            );                       
            
            $userFundsAmount = $userFunds[0]->amount;            

            $transaction_history = TransactionHistory::all();            
            $count = TransactionHistory::count('nav');
            $total = TransactionHistory::sum('nav');
            $investment = TransactionHistory::sum('invest');
            $withdraw = TransactionHistory::sum('withdraw');
            $current_nav = $details['dataset']['data'][1][1];
            
            $data = array('current_nav'=>$current_nav, 'count'=>$count,'total'=>$total ,'investment'=>$investment, 'withdraw'=>$withdraw,'InvestedAmount' => $userFundsAmount,'currentMutualFund' => $block,'walletDetails' => $walletDetails,'details' => $details, 'block' => $blockInfo, 'transactions' => $transactions);
            
            return View::make('explorer.block', $data);

        } catch(Exception $e) {
            $data = array(
                "title"    => "Bitcoin Block",
                "subtitle" => "Could Not Get Block Data",
                "message" => $e->getMessage(),
            );
            return View::make('error.general', $data);
        }
    }

    public function investBitCoin()
    {
        
        try {
            $mid = Input::get('fundId');
            $btc = Input::get('btc');
            $uid = Auth::user()->id;

            $userFunds = DB::table('user_mutual_fund')->where('uid', $uid)->where('mid', $mid)->get();
            $api = $userFunds[0]->api;

            $client = new GuzzleHttp\Client();            
            $shareDetails = file_get_contents($api);   
            $details = json_decode($shareDetails, true);

            $Netvalue = $details['dataset']['data'][0][1];
            $userMutual = UserMutual::where('uid','=',$uid)->where('mid','=',$mid)->first();
            $update_invest_amount = $userMutual->amount + $btc;

            $userMutualFundUnits = $update_invest_amount / $Netvalue;

            $user = UserWallet::where('uid','=',$uid)->first();
            $update_btc = $user->bitcoin - $btc;

            //DateTime of transaction
            $transaction_at = date('Y-m-d H:i:s');
            
            UserWallet::where('uid', '=', $uid)->update(array('bitcoin' => $update_btc));
            UserMutual::where('uid', '=', $uid)->where('mid','=',$mid)->update(array('units' => $update_invest_amount, 'nav' => $Netvalue, 'units' => $userMutualFundUnits, 'amount' => $update_invest_amount));
            
            TransactionHistory::insert(array('nav' => $Netvalue, 'invest' => $btc, 'uid' => $uid, 'mid' => $mid, 'transaction_at' => $transaction_at));

            $this->addUserBlock($mid, $uid, $btc);
            
            return Redirect::route('explorer');

        } catch(Exception $e) {
            $data = array(
                "title"    => "Bitcoin Transaction",
                "subtitle" => "Could Not Get Transaction Data",
                "message" => $e->getMessage(),
            );
            return View::make('error.general', $data);
        }
    }

    public function withdrawBitCoin() {

        try {
            $mid = Input::get('fundId');
            $btc = Input::get('btc');
            $uid = Auth::user()->id;

            $userFunds = DB::table('user_mutual_fund')->where('uid', $uid)->where('mid', $mid)->get();
            $api = $userFunds[0]->api;

            $client = new GuzzleHttp\Client();            
            $shareDetails = file_get_contents($api);   
            $details = json_decode($shareDetails, true);

            $Netvalue = $details['dataset']['data'][0][1];
            
            $userMutual = UserMutual::where('uid','=',$uid)->where('mid','=',$mid)->first();
            $update_invest_amount = $userMutual->amount - $btc;

            $userMutualFundUnits = $update_invest_amount / $Netvalue;

            $user = UserWallet::where('uid','=',$uid)->first();
            $update_btc = $user->bitcoin + $btc;

            //DateTime of transaction
            $transaction_at = date('Y-m-d H:i:s');
            
            UserWallet::where('uid', '=', $uid)->update(array('bitcoin' => $update_btc));
            UserMutual::where('uid', '=', $uid)->where('mid','=',$mid)->update(array('units' => $update_invest_amount, 'nav' => $Netvalue, 'units' => $userMutualFundUnits, 'amount' => $update_invest_amount));
            
            TransactionHistory::insert(array('nav' => $Netvalue, 'withdraw' => $btc,  'uid' => $uid, 'mid' => $mid, 'transaction_at' => $transaction_at));

            $this->addUserBlock($mid, $uid, 0 , $btc);
            
            return Redirect::route('dashboard');
            
        } catch(Exception $e) {
            $data = array(
                "title"    => "Bitcoin Transaction",
                "subtitle" => "Could Not Get Transaction Data",
                "message" => $e->getMessage(),
            );
            return View::make('error.general', $data);
        }
    }

    public function addUserBlock($sid, $uid, $inv_amt, $withdraw=0, $jsonData='') {

		$user_id = Auth::user()->id;
		
		$scheme = UserMutualBlock::where('sid', $sid)->where('uid', $uid)->get();
		
		if(sizeof($scheme) > 0) {
		
			$amount = $scheme[0]->amount;
            if($withdraw > 0) {
                UserMutualBlock::where('sid', $sid)->where('uid', $uid)->update(array('amount'=> $amount - $withdraw));    
            } else {
                UserMutualBlock::where('sid', $sid)->where('uid', $uid)->update(array('amount'=> $amount + $inv_amt));
            }
		
		} else {
		
		$sqlQuery = "SELECT `index` FROM  `userMutualBlock` WHERE  `sid` =$sid ORDER BY  `userMutualBlock`.`index` DESC LIMIT 1";	
		
		$index = DB::select(DB::raw($sqlQuery));
		
		$indexArray = json_decode(json_encode($index),true);
		
		$index = $indexArray[0]['index'];
		
		if($index >= 0) {	
			$index++;
		}	
		
		$block = new BlockChainController();
		
		$date =date('Y-m-d');

		$jsonData = array(
			"user_id" => $uid,
			"scheme_id" => $sid ,
			"amount" => $inv_amt,
		);
		
		$blocks = UserMutualBlock::where('sid',$sid)->orderby('index', 'ASC')->get();
		$blockArray = json_decode($blocks);
		foreach($blockArray as $key=>$value){
			if($key > 0) {
				$existData = array(
					"user_id" => $value->uid,
					"scheme_id" => $value->sid ,
					"amount" => $value->amount,
				);
				$block->addBlock(new BlockController($value->index, $value->created_at, json_decode(json_encode($existData))));
			}
		}
		
		$block->addBlock(new BlockController($index, $date, json_decode(json_encode($jsonData))));
		$schemeDetails = new UserMutualBlock();
		$schemeDetails->index = $index;
		$schemeDetails->uid = $uid;
		$schemeDetails->sid = $sid;
		$schemeDetails->amount = $inv_amt;
		$schemeDetails->save();
		
        return json_encode($block,null,5);
		}
    }
    
    public function scheme() {
        $amount = Input::get('scheme');
        $mid = Input::get('mid');
        $uid = Auth::user()->id;
        $userFunds = DB::table('schemes')->where('id', $mid)->get();

        //Funds Name
        $fundName = $userFunds[0]->name;

        //API  
        $api = $userFunds[0]->api;

        //Net asset value
        $client = new GuzzleHttp\Client();            
        $shareDetails = file_get_contents($api);   
        $details = json_decode($shareDetails, true);
        $Netvalue = $details['dataset']['data'][0][1];

        //Units
        $units = $amount / $Netvalue;

        $transaction_at = date('Y-m-d H:i:s');

        $user = UserWallet::where('uid','=',$uid)->first();
        $deducted_amount = $user->bitcoin - $amount; 
        UserWallet::where('uid', '=', $uid)->update(array('bitcoin' =>  $deducted_amount));
        UserMutual::insert(array('name' => $fundName, 'uid' => $uid, 'mid' => $mid, 'amount' => $amount, 'api' => $api, 'nav' => $Netvalue, 'units' => $units));
        TransactionHistory::insert(array('nav' => $Netvalue, 'invest' => $amount, 'uid' => $uid, 'mid' => $mid, 'transaction_at' => $transaction_at));
        
        $blockChain = json_decode($this->addUserBlock($mid, $uid, $amount),true);

        return View::make('blockchain.blockchain', array('chain' => $blockChain['chain']));
       
    }

    public function search()
    {
        if(Input::get('query')) {
            //detect what is being searched for
            $query = Input::get('query');

            $addressRegex = "/^[123][a-km-zA-HJ-NP-Z0-9]{25,34}$/i";
            $blockHashRegex = "/^[0-9a-f]{64}$/i";
            $txHashRegex = "/^[0-9a-f]{64}$/i";
            $blockHeightRegex = "/^[0-9]+$/i";

            if(preg_match($addressRegex, $query)) {
                //go to address
                return Redirect::route('address', $query);
            } else if( (preg_match($blockHashRegex, $query) && strpos($query, "00000000") === 0) || preg_match($blockHeightRegex, $query)) {
                //go to block
                return Redirect::route('block', $query);
            } else if(preg_match($txHashRegex, $query)) {
                //go to transaction
                return Redirect::route('transaction', $query);
            } else {
                //no matching pattern
                $data = array(
                    "title"    => "",
                    "subtitle" => 'No search results for "'.$query.'"',
                    "message" => "please check your input, it doesn't appear to be an address, block hash or block height",
                );
                return View::make('error.search', $data);
            }
        }

        //no search input
        $data = array(
            "title"    => "",
            "subtitle" => "No search results ",
            "message" => "please enter an address hash, block hash or height",
        );
        return View::make('error.search', $data);
    }

}
