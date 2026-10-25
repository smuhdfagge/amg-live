@extends('layouts.app', ['title' => 'Get involved', 'description' => 'Volunteer with SWATCH in Kano, partner with us on a programme, or support our work with women, girls and children.'])
@php use App\Models\VolunteerApplication as V; @endphp

@section('content')
@include('partials.page-head', [
    'heading' => 'Get involved',
    'intro' => 'More than 80 volunteers work with us across Kano. You can join them, bring your organisation in as a partner, or support a programme directly.',
])

<section class="section section-mist">
    <div class="wrap">
        <div class="ways">
            <div class="way">
                <span class="way-icon"><x-icon name="hand" :size="26" /></span>
                <h3>Volunteer</h3>
                <p>Help in the field, support our digital and data work, or join our advocacy. Training is provided.</p>
                <a class="btn btn-indigo btn-sm" href="#volunteer">Apply below</a>
            </div>
            <div class="way">
                <span class="way-icon"><x-icon name="handshake" :size="26" /></span>
                <h3>Partner</h3>
                <p>Development agencies, foundations, companies and government bodies can work with us to design and deliver programmes.</p>
                <a class="btn btn-indigo btn-sm" href="{{ route('contact', ['type' => 'partnership']) }}#message">Contact our team</a>
            </div>
            <div class="way">
                <span class="way-icon"><x-icon name="gift" :size="26" /></span>
                <h3>Support</h3>
                <p>Fund a programme, sponsor a child's education or give in kind. We report back on how your support is used.</p>
                <a class="btn btn-indigo btn-sm" href="{{ route('support') }}">Ways to give</a>
            </div>
        </div>
    </div>
</section>

<section class="section anchor-offset" id="volunteer">
    <div class="wrap split">
        <div>
            <h2>Volunteer with SWATCH</h2>
            <p class="lead">Our volunteers are part of every programme. They visit households, run community sessions, follow up referrals and keep our records accurate.</p>
            <h3 class="mt-2">What to expect</h3>
            <ol class="prose" style="padding-left:1.2rem">
                <li>Send in the form. It takes about five minutes.</li>
                <li>Our volunteer coordinator calls you to talk about your interests and availability.</li>
                <li>You attend an orientation on our safeguarding rules, values and ways of working.</li>
                <li>We match you to a programme or team that needs your skills.</li>
            </ol>
            <p class="muted small">Volunteers who work directly with children must agree to our safeguarding policy and may be asked for references.</p>
        </div>

        <div class="form-panel">
            @if (session('volunteer_status'))
                <div class="notice notice-success" role="status"><x-icon name="check" :size="22" /><span>{{ session('volunteer_status') }}</span></div>
            @endif
            @if ($errors->any())
                <div class="notice notice-error" role="alert">Some details need your attention. Check the highlighted fields.</div>
            @endif
            <form method="POST" action="{{ route('volunteer.send') }}" novalidate>
                @csrf
                <div class="hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
                <div class="form-grid">
                    @include('partials.field', ['name' => 'name', 'id' => 'v-name', 'label' => 'Full name', 'required' => true, 'autocomplete' => 'name', 'class' => 'full'])
                    @include('partials.field', ['name' => 'email', 'id' => 'v-email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'autocomplete' => 'email'])
                    @include('partials.field', ['name' => 'phone', 'id' => 'v-phone', 'label' => 'Phone number', 'type' => 'tel', 'required' => true, 'autocomplete' => 'tel'])
                    @include('partials.field', ['name' => 'location', 'id' => 'v-location', 'label' => 'LGA or town'])
                    @include('partials.field', ['name' => 'availability', 'id' => 'v-availability', 'label' => 'Availability', 'type' => 'select', 'options' => V::AVAILABILITY, 'placeholder' => 'Choose one'])
                    @include('partials.field', ['name' => 'interest', 'id' => 'v-interest', 'label' => 'Where would you like to help?', 'type' => 'select', 'options' => V::INTERESTS, 'placeholder' => 'Choose an area', 'required' => true, 'class' => 'full'])
                    @include('partials.field', ['name' => 'skills', 'id' => 'v-skills', 'label' => 'Your skills and experience', 'type' => 'textarea', 'class' => 'full'])
                    @include('partials.field', ['name' => 'message', 'id' => 'v-message', 'label' => 'Anything else we should know?', 'type' => 'textarea', 'class' => 'full'])
                    <div class="full"><button class="btn btn-red" type="submit">Send application</button></div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
