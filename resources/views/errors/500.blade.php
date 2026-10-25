@extends('layouts.app', ['title' => 'Something went wrong on our side', 'hideCta' => true])

@section('content')
<section class="page-head" style="min-height:52vh;display:flex;align-items:center">
    <div class="page-head-laurel">@include('partials.laurel')</div>
    <div class="wrap">
        <h1>Something went wrong on our side</h1>
        <p class="lead">Please try again in a few minutes. If the problem continues, call us on 0803 787 3565.</p>
        <div class="btn-row mt-2">
            <a class="btn btn-indigo" href="{{ route('home') }}">Go to the home page</a>
            <a class="btn btn-outline" href="{{ route('projects.index') }}">See our programmes</a>
            <a class="btn btn-outline" href="{{ route('contact') }}">Contact us</a>
        </div>
    </div>
</section>
@endsection
