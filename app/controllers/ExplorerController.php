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
         $transaction_history = new TransactionHistory();
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

     public function history()
     {
         //get the latest few blocks
         $transaction_history = TransactionHistory::with('user')->get();
         $data = array('transaction_history' => $transaction_history);
         // echo "<pre>";
         // print_r($data); exit;
         return View::make('history', $data);
     
     }

    public function showHome()
    {
        try {
            $client = new GuzzleHttp\Client();
            $shareDetails = file_get_contents("https://www.quandl.com/api/v3/datasets/AMFI/142014.json?api_key=JAvVoWLCx4sxcL2Y3hM1");
            $details = json_decode($shareDetails, true);
            //get the latest few blocks
            $userFunds = UserMutual::all();
            $schemeDetails = Schemes::all();
            
            $blocks = $this->bitcoinClient->allBlocks($page = 1, $limit = 5, $sortDir = 'desc');

            $userFunds = UserMutual::all();

            //create the view, passing the data
            $data = array('details' => $details, 'blocks' => $blocks, 'userFunds' => $userFunds, 'schemeDetails' => $schemeDetails,);
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
            $client = new GuzzleHttp\Client();
            $shareDetails = file_get_contents("https://www.quandl.com/api/v3/datasets/AMFI/142014.json?api_key=JAvVoWLCx4sxcL2Y3hM1");        
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
                'totalUncBalance' => $totalUncBalance,
            );
            $userFunds = UserMutual::find($block);
            $data = array('userFunds' => $userFunds, 'walletDetails' => $walletDetails,'details' => $details, 'block' => $blockInfo, 'transactions' => $transactions);
            
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
