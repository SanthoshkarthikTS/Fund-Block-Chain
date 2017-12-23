@extends('layouts.master')

@section('title')
    A Simple Block Explorer
@stop

@section('content')

<section>
    <div class="container">
        <h3 class="section-heading">Trusted investers</h3>
        <table class="u-full-width blocks">
            <thead>
                <tr>
                    <th><div>Name</div></th>
                    <th><div>Location</div></th>
                    <th><div>Amount</div></th>
                    <th><div>Invest</div></th>
                </tr>
            </thead>
            <tbody>

                @foreach ($schemeDetails as $block)
                    <tr>
                        <td>{{$block['name']}}</td>
                        <td>{{$block['location']}}</td>
                        <form id="investForm" action="scheme" method="post">
                            <input type="hidden" value="{{$block['id']}}" name="uid">
                            <input type="hidden"  value="{{$block['id']}}"  name="mid" placeholder="BTC">
                            <td><input type="text" style="width:60px;height:20px" name="scheme"></td>
                            <td><button type="submit" class="button-primary">invest </button></td>
                        </form>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>


    <section>
        <div class="container">
            <h1>A Simple Block Explorer</h1>
            <p>
                A working example of the <a href="https://www.blocktrail.com/" target="_blank">BlockTrail API</a> used to create a simple Bitcoin block explorer.
            </p>
            {{ Form::open(array('route' => 'search', 'method' => 'get')) }}
                <div class="row">
                    <div class="ten columns">
                        {{ Form::text('query', null, array('placeholder' => 'address, block hash/height or transaction', 'class' => 'u-full-width')) }}
                    </div>
                    <div class="two columns">
                        {{ Form::submit('search', array('class' => 'button button-primary')) }}
                    </div>
                </div>
            {{ Form::close() }}
        </div>
    </section>

    <section>
        <div class="container">
            <h3 class="section-heading">List Of Funds Invested</h3>
            <table class="u-full-width blocks">
                <thead>
                    <tr>
                        <th><div>User ID</div></th>
                        <th><div>Industry ID</div></th>
                        <th><div>Amount Invested</div></th>
                        <th><div>NAV</div></th>
                        <th><div>Units</div></th>
                    </tr>
                </thead>
                <tbody>
                    @if(count($userFunds) > 0)
                        @foreach ($userFunds as $block)
                        <tr>
                            <td>#{{$block['uid']}}</td>
                            <td>{{$block['name']}}</td>
                            <td>{{$block['amount']}}</td>
                            <td>{{$block['nav']}}</td>
                            <td><a href="{{ URL::route('block', $block['mid']) }}">View Block</a></td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </section>

    <section>
        <div class="container">
            <hr>
            <h3 class="section-heading">Need help getting started?</h3>
            <p class="section-description">BlockTrail is an amazingly easy place to start with Bitcoin development. If you want to learn more, just visit the documentation!</p>
            <a class="button button-primary" href="https://www.blocktrail.com/api/docs" target="_blank">View BlockTrail Docs</a>
        </div>
    </section>

    <section></section>
@stop