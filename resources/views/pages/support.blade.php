@extends('layouts.app', ['title' => 'Support our work', 'description' => 'Ways to support the Aminu Magashi Garba Foundation: fund a programme, support a patient, give in kind or make a direct gift.'])
@php $bank = config('amg.bank'); $c = config('amg.contact'); @endphp

@section('content')
@include('partials.page-head', [
    'heading' => 'Support our work',
    'intro' => 'Your support reaches vulnerable people through work that is planned, monitored and reported on.',
])

<section class="section">
    <div class="wrap">
        <div class="section-head">
            <div>
                <h2>Ways to support</h2>
                <p>Choose the option that suits you or your organisation. We will talk through the details with you.</p>
            </div>
        </div>
        <div class="methods">
            <div>
                <h3>Fund a programme</h3>
                <p>Back a full programme or one part of it, in any of our focus areas. We agree targets and reporting with you at the start.</p>
            </div>
            <div>
                <h3>Support a patient</h3>
                <p>Help a person living with sickle cell disease, cancer or another health challenge to get the treatment and care they need.</p>
            </div>
            <div>
                <h3>Back a livelihood</h3>
                <p>Give a woman, a young person or a person with disability the skills and start-up support to earn a living.</p>
            </div>
            <div>
                <h3>Give in kind</h3>
                <p>Mobility aids, medical supplies, food items, hygiene kits and equipment all help our work go further.</p>
            </div>
            <div>
                <h3>Share your expertise</h3>
                <p>Professionals in health, finance, law, data and communications can offer pro bono time to strengthen our work.</p>
            </div>
            <div>
                <h3>Corporate partnership</h3>
                <p>Companies can link their social investment to clear results for vulnerable people and communities.</p>
            </div>
        </div>
    </div>
</section>

<section class="section section-mist">
    <div class="wrap split">
        <div>
            <h2>Give directly</h2>
            @if (filled($bank['account_number']))
                <p class="lead">You can send a gift by bank transfer. Please email us after your transfer so we can thank you and send a receipt.</p>
                <dl class="registration" style="flex-direction:column;gap:1rem">
                    <div><div><dt>Account name</dt><dd>{{ $bank['account_name'] }}</dd></div></div>
                    <div><div><dt>Account number</dt><dd>{{ $bank['account_number'] }}</dd></div></div>
                    <div><div><dt>Bank</dt><dd>{{ $bank['bank_name'] }}</dd></div></div>
                </dl>
            @else
                <p class="lead">To make a gift, contact us. We will send you our official account details and confirm receipt of every gift in writing.</p>
                <div class="btn-row">
                    <a class="btn btn-red" href="{{ route('contact', ['type' => 'support']) }}#message">Request account details</a>
                    @if (filled($c['phone']))<a class="btn btn-outline" href="tel:{{ $c['phone_link'] }}">Call {{ $c['phone'] }}</a>@endif
                </div>
            @endif
            <p class="muted small mt-2">For your safety, the Foundation only receives funds into accounts registered in the organisation's name. We will never ask you to pay into a personal account.</p>
        </div>
        <div>
            <h3>How we account for your support</h3>
            <ul class="checklist">
                <li><x-icon name="check" :size="20" /> Every gift is recorded and acknowledged in writing</li>
                <li><x-icon name="check" :size="20" /> Restricted gifts are spent only on the purpose you choose</li>
                <li><x-icon name="check" :size="20" /> Programme partners receive progress and financial reports on the agreed schedule</li>
                <li><x-icon name="check" :size="20" /> Our registration documents and accounts are available on request</li>
            </ul>
        </div>
    </div>
</section>

@if ($featured->isNotEmpty())
<section class="section">
    <div class="wrap">
        <div class="section-head">
            <div>
                <h2>The kind of work your support makes possible</h2>
                <p>Some of the programmes we run with our partners.</p>
            </div>
            <a class="text-link" href="{{ route('projects.index') }}">All programmes</a>
        </div>
        <div class="programmes">
            @foreach ($featured as $project)
                @include('partials.programme-card', ['project' => $project])
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
