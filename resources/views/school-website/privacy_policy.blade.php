@extends('layouts.school.master')
@section('title')
    {{ __('privacy_policy') }}
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
                <span class="title"> {{ __('privacy_policy') }} </span>
                <span dir="{{ $dir }}">
                    <a dir="{{ $dir }}" href="{{ url('/') }}" class="home">{{ __('home') }}</a>
                    <span><i class="fa-solid fa-caret-right"></i></span>
                    <span class="page">{{ __('privacy_policy') }}</span>
                </span>
            </div>
        </div>
    </div>

    <section class="aboutUs commonMT commonWaveSect">
        <div class="container">
            <div class="row aboutWrapper">
                <div class="title text-center">
                    <h1>{{ __('privacy_policy') }}</h1>
                </div>

                <div class="col-sm-12 col-md-12">
                    @if (isset($schoolSettings['privacy_policy']))
                        <div class="custom">
                            {!! htmlspecialchars_decode($schoolSettings['privacy_policy'] ??  __('privacy_policy_is_not_available_at_the_moment')) !!}
                        </div>
                    @else
                        <div class="text-center">
                            <span>{{ __('privacy_policy_is_not_available_at_the_moment') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

@endsection