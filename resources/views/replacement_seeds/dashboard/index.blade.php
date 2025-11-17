{{-- resources/views/replacement_seeds/dashboard.blade.php --}}
@extends('replacement_seeds.layouts.index')

@section('content')
<div class="page-title">
    <h2>Welcome, {{ $user->firstName }}</h2>
</div>

<h4>Your Roles:</h4>
@if(count($user->roles))
    <ul>
        @foreach($user->roles as $role)
            <li>{{ $role->display_name ? $role->display_name : $role->name }}</li>
        @endforeach
    </ul>
@else
    <p>No roles assigned.</p>
@endif

@if($user->hasRole('admin'))
    <p>Welcome, system administrator!</p>
@endif

<form method="POST" action="{{ route('replacement.logout') }}">
    {{ csrf_field() }}
    <button type="submit">Logout</button>
</form>
@endsection