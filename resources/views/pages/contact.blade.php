@extends('layouts.app', ['title' => 'Contact us', 'description' => 'Contact SWATCH at Plot 203, New Court Road, Gyadi-Gyadi, Kano. Call 0803 787 3565 or email swatchngo2002@gmail.com.', 'hideCta' => true])
@php $c = config('swatch.contact'); @endphp

@section('content')
@include('partials.page-head', [
    'heading' => 'Contact us',
    'intro' => 'Call, email or visit our office in Kano. You can also send us a message below and we will reply within three working days.',
])

<section class="section">
    <div class="wrap split">
        <div>
            <ul class="contact-list">
                <li><span class="ci"><x-icon name="pin" :size="22" /></span><div><strong>Office</strong><span>{{ $c['address_line_1'] }}, {{ $c['address_line_2'] }}, {{ $c['country'] }}</span></div></li>
                <li><span class="ci"><x-icon name="phone" :size="22" /></span><div><strong>Phone</strong><a href="tel:{{ $c['phone_link'] }}">{{ $c['phone'] }}</a></div></li>
                <li><span class="ci"><x-icon name="mail" :size="22" /></span><div><strong>Email</strong><a href="mailto:{{ $c['email'] }}">{{ $c['email'] }}</a><br><a href="mailto:{{ $c['email_alt'] }}">{{ $c['email_alt'] }}</a></div></li>
                <li><span class="ci"><x-icon name="clock" :size="22" /></span><div><strong>Office hours</strong><span>{{ $c['hours'] }}</span></div></li>
            </ul>
            <div class="map">
                <iframe title="Map showing the SWATCH office in Gyadi-Gyadi, Kano" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://maps.google.com/maps?q={{ urlencode($c['map_query']) }}&z=15&output=embed"></iframe>
            </div>
        </div>

        <div class="form-panel anchor-offset" id="message">
            <h2 style="font-size:1.6rem">Send us a message</h2>
            @if (session('status'))
                <div class="notice notice-success" role="status"><x-icon name="check" :size="22" /><span>{{ session('status') }}</span></div>
            @endif
            @if ($errors->any())
                <div class="notice notice-error" role="alert">Some details need your attention. Check the highlighted fields.</div>
            @endif
            <form method="POST" action="{{ route('contact.send') }}#message" novalidate>
                @csrf
                <div class="hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
                <div class="form-grid">
                    @include('partials.field', ['name' => 'type', 'label' => 'What is this about?', 'type' => 'select', 'options' => \App\Models\ContactMessage::TYPES, 'value' => request('type', 'general'), 'required' => true, 'class' => 'full'])
                    @include('partials.field', ['name' => 'name', 'label' => 'Full name', 'required' => true, 'autocomplete' => 'name'])
                    @include('partials.field', ['name' => 'organisation', 'label' => 'Organisation', 'autocomplete' => 'organization'])
                    @include('partials.field', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'autocomplete' => 'email'])
                    @include('partials.field', ['name' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'autocomplete' => 'tel'])
                    @include('partials.field', ['name' => 'subject', 'label' => 'Subject', 'class' => 'full'])
                    @include('partials.field', ['name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'required' => true, 'class' => 'full'])
                    <div class="full">
                        <button class="btn btn-red" type="submit">Send message</button>
                        <p class="small muted mt-0" style="margin-top:.9rem">We use your details only to reply to you. See our <a href="{{ route('privacy') }}">privacy notice</a>.</p>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
