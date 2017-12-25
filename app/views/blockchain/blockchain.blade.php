@extends('layouts.master')

@section('title')
    Explorer - Block 
@stop

@section('content')
<section class="block-transactions">
        <div class="container">
            <div class="row">
            <h2>Block Chain</h2>
            <?php 
            // $test = '{"chain":[{"_data":null,"_hash":null,"index":0,"timestamp":"01\/01\/2017","data":{"name": "Genesis block"},"previousHash":"0","hash":"27919f9905839e3dabfe836d1daf9236fc4e753214b9455a78bf04b2fa99e399"},{"_data":null,"_hash":null,"index":1,"timestamp":"2017-12-25","data":{"user_id":9,"scheme_id":"6","amount":"567"},"previousHash":"27919f9905839e3dabfe836d1daf9236fc4e753214b9455a78bf04b2fa99e399","hash":"0e4c032d4e9611c9ee60e304d1ddefddd7469e64c7078dfcaa95d25da250d382"}]}';
            
            //       $decode = json_decode($test, true);
            //       //print_r($decode['chain']);exit;
            ?>                     
            <div class="div-table">
                <div class="div-table-row">
                    <div class="div-table-col heading" align="center">Index</div>
                    <div  class="div-table-col heading" align="center">Date</div>
                    <div  class="div-table-col heading" align="center">Data</div>
                    <div  class="div-table-col heading" align="center">Previous Hash</div>
                    <div  class="div-table-col heading" align="center">Hash</div>
                </div>
                @if(!empty($chain))
                    @foreach($chain as $block)
                    <div class="div-table-row">
                        <div class="div-table-col">{{$block['index']}}</div>
                        <div class="div-table-col">{{$block['timestamp']}}</div>
                        <div class="div-table-col">
                            <?php
                        foreach($block['data'] as $key=>$val) {
                            print_r("<strong>".$key."</strong>" . "-" . $val."<br>");
                        } 
                        ?>
                        </div>
                        <div class="div-table-col"><span class="input"></span>{{$block['previousHash']}}</span></div>
                        <div class="div-table-col">{{$block['hash']}}</div>
                    </div>
                    @endforeach
                @endif 
                </div>
            </div>
        </div>
    </section>
    @stop