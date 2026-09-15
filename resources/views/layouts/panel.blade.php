@extends('layouts.app')

@section('content')
    <div class="flex">
        @include('layouts.partials._sidebar')

        <main class="min-w-0 flex-1 px-6 py-10 md:px-10">
            @yield('panel')
        </main>
    </div>
@endsection