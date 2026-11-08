@if (session('status'))
    <p class="flash">{{ session('status') }}</p>
@endif
@if ($errors->any())
    <div class="flash error">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif
