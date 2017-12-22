@extends('layouts.master')

@section('title')
    Explorer - Block {{$block['hash']}}
@stop

@section('content')

    <section>
        <div class="container">
            @foreach ($details as $funds)
            <h4><strong>{{$funds['name']}}</strong></h4>
            @endforeach
            <div>{{$block['hash']}}</div>
        </div>
    </section>

    <section>
        <div class="container">
            <h2 class="section-heading">Summary</h2>
            <div class="row">
            @foreach ($details as $funds)
                <div class="three columns"><b>Date: </b>{{$funds['data'][0][0]}}</div>
                <div class="three columns"><b>Net Express value:</b> {{$funds['data'][0][1]}}</div>
                <div class="three columns"><b>Transactions:</b> {{$block['transactions']}}</div>
                <div class="three columns"><b>Size:</b> {{$block['byte_size']}} bytes</div>
            @endforeach
            </div>
            <div class="row">
                <div class="three columns"><b>Confirmations:</b> {{$block['confirmations']}}</div>
                <div class="three columns"><a href="#openModal">Invest</a></div>
                
                <div id="openModal" class="modalDialog">
                    <div>
                        <a href="#close" title="Close" class="close">X</a>
                        <form id="loginForm" action="/whatever" method="post">
                            <div class="row">
                                <div class="seven columns">
                                    <label for=""></label>
                                    @foreach($walletDetails as $wallet) 
                                    <input type="text" name="wallet" id="wallet" placeholder="Balance:{{$wallet}}BTC" disabled/>
                                    @endforeach
                                    @foreach ($details as $funds)
                                    <input type="text" name="email" id="emailField" placeholder="name@example.com" value="{{$funds['data'][0][1] * $userFunds->units}}" readonly required/>
                                    @endforeach
                                    
                                    <input type="text" name="Amount" id="amount" placeholder="Rs." required/>
                                    
                                    <button type="submit" class="button-primary">Invest</button>
                                </div>
                            </div>
                        </form>    
                    </div>
                </div>

            </div>
            <div class="row margin-t">
                <div class="three columns">
                    @if($block['prev_block'])
                    <b>Previous: </b>#<a href="{{ URL::route('block', $block['prev_block']) }}">{{$block['height']-1}}</a>
                    @endif
                </div>
                <div class="three columns">
                    @if($block['next_block'])
                    <b>Next: </b>#<a href="{{ URL::route('block', $block['next_block']) }}">{{$block['height']+1}}</a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="block-transactions">
        <div class="container">
            <div class="row">
                <div class="one-third column">
                    <h2>Transactions</h2>
                    <div class="row">
                        <div><b>Total Transactions:</b> {{$block['transactions']}}</div>
                        <div><b>Value:</b> <span class="btc-value">@toBTC($block['value'])</span> BTC</div>
                    </div>
                </div>
                <div class="two-thirds column">
                    <div class="scroll-window">
                        <table class="u-full-width fixed-header transactions">
                            <thead>
                                <tr>
                                    <th><div>Total Investments</div></th>
                                    <th><div>Total Output</div></th>
                                    <th><div>Transaction Fee</div></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <td>Total Input</td>
                                    <td>Total Output</td>
                                    <td>Transaction Fee</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                            <tbody>
                                @foreach ($transactions as $tx)
                                <tr>
                                    <td>{{$tx['total_input_value']}}</td>
                                    <td>{{$tx['total_output_value']}}</td>
                                    <td class="input">{{$tx['total_fee']}}</td>
                                    <td><a href="{{ URL::route('transaction', $tx['hash']) }}">view transaction</a></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </section>
@stop
