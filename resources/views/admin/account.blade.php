@extends('admin.layout', ['title' => 'My account'])

@section('content')
<div class="admin-top"><h1>My account</h1></div>
<form method="POST" action="{{ route('admin.account.update') }}" class="panel" style="max-width:720px">
    @csrf @method('PUT')
    <div class="form-grid">
        @include('partials.field', ['name' => 'name', 'label' => 'Name', 'required' => true, 'value' => $user->name])
        @include('partials.field', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'value' => $user->email])
        <div class="field full"><p class="help mt-0">To change your password, enter a new one twice. Leave both empty to keep your current password.</p></div>
        <div class="field @error('password') has-error @enderror"><label for="password">New password <span class="hint">(at least 10 characters)</span></label><input id="password" type="password" name="password" autocomplete="new-password">@error('password')<span class="error">{{ $message }}</span>@enderror</div>
        <div class="field"><label for="password_confirmation">Repeat new password</label><input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password"></div>
        <div class="field full @error('current_password') has-error @enderror"><label for="current_password">Current password <span class="hint">(needed to save any change)</span></label><input id="current_password" type="password" name="current_password" required autocomplete="current-password">@error('current_password')<span class="error">{{ $message }}</span>@enderror</div>
    </div>
    <div class="form-actions"><button class="btn btn-red" type="submit">Save changes</button></div>
</form>
@endsection
