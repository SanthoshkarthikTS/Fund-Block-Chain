@extends('layouts.master')

@section('title')
    Simple Bitcoin Wallet
@stop

@section('sidebar')
@stop

@section('content')


@foreach ($transaction_history as $transaction_history)
@if ($transaction_history['withdraw'] != NULL) 

<p ><strong>Withdrawn:</strong><span style="color:red">-{{$transaction_history['withdraw']}}</span><p>
@endif

@if ($transaction_history['invest'] != NULL)
<p ><strong>Invested:</strong><span style="color:green">+{{$transaction_history['invest']}}</span><p>
@endif

<!---p ><strong>Email ID:</strong><span>-{{$transaction_history['user']['email']}}</span><p--->


@endforeach


@stop

