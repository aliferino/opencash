@if (auth()->user()->isAdmin())
    @include('layouts.partials._sidebar-admin')
@elseif (auth()->user()->isTreasurer())
    @include('layouts.partials._sidebar-treasurer')
@elseif (auth()->user()->isStudent())
    @include('layouts.partials._sidebar-student')
@endif