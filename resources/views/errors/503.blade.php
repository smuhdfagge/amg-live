@extends('layouts.app', ['title' => 'We are updating the website', 'hideCta' => true])

@section('content')
<section class="page-head" style="min-height:52vh;display:flex;align-items:center">
    <div class="page-head-laurel">@include('partials.laurel')</div>
    <div class="wrap">
        <h1>We are updating the website</h1>
        <p class="lead">The site will be back shortly. Thank you for your patience.</p>
        <div class="btn-row mt-2">
            <a class="btn btn-indigo" href="{{ route('home') }}">Go to the home page</a>
            <a class="btn btn-outline" href="{{ route('projects.index') }}">See our programmes</a>
            <a class="btn btn-outline" href="{{ route('contact') }}">Contact us</a>
        </div>
    </div>
</section>
@endsection
