@extends('layouts.master')

@section('title')
    Explorer - Block 
@stop

@section('content')

    <section>
        <div class="container">
            @foreach ($details as $funds)
            <h4><strong>{{$funds['name']}}</strong></h4>
            @endforeach
        </div>
    </section>

    <section>
        <div class="container">
            <h2 class="section-heading">Summary</h2>
            <div class="row">
            @foreach ($details as $funds)
                <div class="three columns"><b>Date: </b>{{$funds['data'][0][0]}}</div>
                <div class="three columns"><b>Net Express value:</b> {{$funds['data'][0][1]}}</div>
            @endforeach
            </div>
            <div class="row">
                <div class="three columns"><a href="#openModal"><button class="button button-primary">Invest</button></a></div>
                
                <div id="openModal" class="modalDialog">
                    <div>
                        <a href="#close" title="Close" class="close">X</a>
                        <form id="loginForm" action="/investBTC" method="post">
                            <div class="row">
                                <div class="seven columns">
                                    <label for="">Balance</label>
                                    @foreach($walletDetails as $wallet) 
                                    <input type="text" name="wallet" id="wallet" placeholder="BTC : {{$wallet}}" disabled/>
                                    @endforeach                                       
                                    {{  Form::open(array('action'=>'ExplorerController@investBitCoin', 'method' => 'post')) }}
                                        <label for="">User Mutual Fund Id</label>                                        
                                        <td>{{  Form::text('fundId', $currentMutualFund,   array('readonly' => 'true'))  }}</td>
                                        <label for="">Invest Here</label>                                        
                                        <td>{{  Form::text('btc', Input::old('btc'),  array('placeholder'=>'BTC'))  }}</td>
                                        <td>{{ Form::submit('Invest', array('class' => 'button button-primary')) }}</td>
                                    {{  Form::close()  }}                                  
                                </div>
                            </div>
                        </form>    
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="three columns"><a href="#openModal1"><button class="button button-default">Withdraw</button></a></div>
                
                <div id="openModal1" class="modalDialog">
                    <div>
                        <a href="#close" title="Close" class="close">X</a>
                        <form id="loginForm" action="/withdrawBTC" method="post">
                            <div class="row">
                                <div class="seven columns">
                                    <label for="">Invested Amount</label>
                                    <input type="text" name="wallet" id="wallet" placeholder="BTC : {{$InvestedAmount}}" disabled/>
                                    {{  Form::open(array('action'=>'ExplorerController@withdrawBitCoin', 'method' => 'post')) }}
                                        <label for="">User Mutual Fund Id</label>                                        
                                        <td>{{  Form::text('fundId', $currentMutualFund,   array('readonly' => 'true'))  }}</td>
                                        <label for="">Enter BTC</label>                                        
                                        <td>{{  Form::text('btc', Input::old('btc'),  array('placeholder'=>'BTC'))  }}</td>
                                        <td>{{ Form::submit('Withdraw', array('class' => 'button button-primary')) }}</td>
                                    {{  Form::close()  }}                                  
                                </div>
                            </div>
                        </form>    
                    </div>
                </div>
            </div>
        </div>
    </section>
@stop
