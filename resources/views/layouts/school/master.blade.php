<!DOCTYPE html>
@php
    $lang = Session::get('language');
    $dir = 'ltr';
    if ($lang && $lang->is_rtl) {
        $dir = 'rtl';
    }else {
        $dir = 'ltr';
    }
@endphp
@if($lang)
    @if ($lang->is_rtl)
        <html lang="en" dir="rtl">
    @else
        <html lang="en" dir="ltr">
    @endif
@else
    <html lang="en" dir="ltr">
@endif
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <title>
        @yield('title') || 
        {{-- {{ config('app.name') }} --}}
        {{ $schoolSettings['school_name'] ?? 'eSchool - Saas' }}
    </title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('layouts.school.include')
    @yield('css')
</head>
<body style="background-color: #F2F5F7">
    {{-- header --}}
    @include('layouts.school.header')
    <div class="main">
        @php
        $lang = Session::get('language');
        $dir = 'ltr';
        if ($lang && $lang->is_rtl) {
            $dir = 'rtl';
        }else {
            $dir = 'ltr';
        }
    @endphp
        @yield('content')
    </div>
    <script>
        let originalFormat = "{{ $originalDateFormat }}";
        let jsReplacedDateFormat = originalFormat; // reset every time
        jsReplacedDateFormat = jsReplacedDateFormat
            .replace(/d/g, "DD")
            .replace(/m/g, "MM")
            .replace(/Y/g, "YYYY");
        let datepickerReplacedFormat = originalFormat
        datepickerReplacedFormat = datepickerReplacedFormat
            .replace(/d/g, "dd")
            .replace(/m/g, "mm")
            .replace(/Y/g, "yyyy");
    </script>

    {{-- footer --}}
@include('layouts.school.footer')
@include('layouts.school.footer_js')
@yield('js')
@yield('script')
</body>
</html>
