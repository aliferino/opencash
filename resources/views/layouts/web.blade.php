@extends('layouts.app')

@section('content')
    @include('layouts.partials._navbar')

    <main>
        @yield('web')
    </main>

    @include('layouts.partials._footer')
@endsection