@extends('layouts.app', ['title' => 'Privacy notice'])

@section('content')
@include('partials.page-head', ['heading' => 'Privacy notice', 'intro' => 'How SWATCH handles the personal information you give us through this website.'])

<section class="section">
    <div class="wrap">
        <div class="prose">
            <h2>What we collect</h2>
            <p>When you send a message, apply to volunteer or subscribe to updates, we collect the details you enter: usually your name, email address, phone number and the content of your message.</p>
            <h2>How we use it</h2>
            <ul>
                <li>To reply to your enquiry</li>
                <li>To process your volunteer application</li>
                <li>To send you updates, if you subscribed</li>
            </ul>
            <p>We do not sell or rent your information to anyone. We share it only with staff who need it to respond to you, or where the law requires us to.</p>
            <h2>How long we keep it</h2>
            <p>We keep messages and applications for as long as we need them to respond and for our records, and then delete them. You can unsubscribe from updates at any time by emailing us.</p>
            <h2>Your rights</h2>
            <p>In line with the Nigeria Data Protection Act 2023, you can ask to see, correct or delete the information we hold about you. Email <a href="mailto:{{ config('swatch.contact.email') }}">{{ config('swatch.contact.email') }}</a> and we will respond within 30 days.</p>
            <h2>Cookies</h2>
            <p>This website uses only the cookies it needs to work, such as the session cookie that protects our forms. We do not use advertising cookies.</p>
        </div>
    </div>
</section>
@endsection
