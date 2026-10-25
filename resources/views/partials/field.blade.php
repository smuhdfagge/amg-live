@php $fid = $id ?? $name; @endphp
<div class="field {{ $class ?? '' }} @error($name, $bag ?? 'default') has-error @enderror">
    <label for="{{ $fid }}">{{ $label }} @if (!($required ?? false))<span class="hint">(optional)</span>@endif</label>
    @if (($type ?? 'text') === 'textarea')
        <textarea id="{{ $fid }}" name="{{ $name }}" @if ($required ?? false) required @endif @error($name) aria-invalid="true" aria-describedby="{{ $fid }}-error" @enderror>{{ old($name, $value ?? '') }}</textarea>
    @elseif (($type ?? 'text') === 'select')
        <select id="{{ $fid }}" name="{{ $name }}" @if ($required ?? false) required @endif @error($name) aria-invalid="true" aria-describedby="{{ $fid }}-error" @enderror>
            @isset($placeholder)<option value="">{{ $placeholder }}</option>@endisset
            @foreach ($options as $val => $text)
                <option value="{{ $val }}" @selected(old($name, $value ?? '') == $val)>{{ $text }}</option>
            @endforeach
        </select>
    @else
        <input id="{{ $fid }}" type="{{ $type ?? 'text' }}" name="{{ $name }}" value="{{ old($name, $value ?? '') }}" @if ($required ?? false) required @endif @isset($autocomplete) autocomplete="{{ $autocomplete }}" @endisset @error($name) aria-invalid="true" aria-describedby="{{ $fid }}-error" @enderror>
    @endif
    @error($name)<span class="error" id="{{ $fid }}-error">{{ $message }}</span>@enderror
</div>
