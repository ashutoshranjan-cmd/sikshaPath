@extends('layouts.school.master')
@section('title')
    {{ __('refund_cancellation') }}
@endsection
@section('css')
    <style>
        .custom ol, .custom ul {
            list-style: revert !important;
        }
    </style>
@endsection
@section('content')
    @php
        $dir = Session::get('language')->is_rtl ? 'rtl' : 'ltr';
    @endphp
    <div class="breadcrumb">
        <div class="container">
            <div class="contentWrapper">
                <span class="title"> {{ __('refund_cancellation') }} </span>
                <span dir="{{ $dir }}">
                    <a dir="{{ $dir }}" href="{{ url('/') }}" class="home">{{ __('home') }}</a>
                    <span><i class="fa-solid fa-caret-right"></i></span>
                    <span class="page">{{ __('refund_cancellation') }}</span>
                </span>
            </div>
        </div>
    </div>

    <section class="aboutUs commonMT commonWaveSect">
        <div class="container">
            <div class="row aboutWrapper">
                <div class="title text-center">
                    <h1>{{ __('refund_cancellation') }}</h1>
                </div>

                <div class="col-sm-12 col-md-12">
                    @if (isset($schoolSettings['refund_cancellation']))
                        <div class="custom">
                            {!! htmlspecialchars_decode($schoolSettings['refund_cancellation'] ?? __('refund_cancellation_is_not_available_at_the_moment')) !!}
                        </div>
                    @else
                        <div class="text-center">
                            <span>{{ __('refund_cancellation_is_not_available_at_the_moment') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

@endsection