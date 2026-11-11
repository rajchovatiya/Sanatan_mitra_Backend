@props(['name', 'label', 'value' => [], 'textarea' => false, 'required' => true, 'translate' => true])
{{-- One input per language for a {"en","hi","gu"} field. The admin form's "Translate" button
     fills Hindi and Gujarati from English unless translate is false (e.g. Sanskrit lyrics). --}}
<fieldset class="langs" data-field="{{ $name }}" data-translate="{{ $translate ? 'on' : 'off' }}">
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
