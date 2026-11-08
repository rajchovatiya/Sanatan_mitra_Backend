@props(['name', 'label', 'value' => [], 'textarea' => false, 'required' => true])
{{-- One input per language for a {"en","hi","gu"} field. --}}
<fieldset class="langs">
    <legend>{{ $label }}</legend>
    @foreach (['en' => 'English', 'hi' => 'हिंदी', 'gu' => 'ગુજરાતી'] as $lang => $langLabel)
        @php($val = old("{$name}.{$lang}", $value[$lang] ?? ''))
        <label>{{ $langLabel }}@if ($lang === 'en' && $required) *@endif
            @if ($textarea)
                <textarea name="{{ $name }}[{{ $lang }}]" rows="5" @required($lang === 'en' && $required)>{{ $val }}</textarea>
            @else
                <input type="text" name="{{ $name }}[{{ $lang }}]" value="{{ $val }}" @required($lang === 'en' && $required)>
            @endif
        </label>
    @endforeach
</fieldset>
