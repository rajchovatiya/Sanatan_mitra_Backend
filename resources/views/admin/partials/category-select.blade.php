<label>Category *
    <select name="category_id" required>
        <option value="">Choose…</option>
        @foreach ($categories as $c)
            <option value="{{ $c->id }}" @selected((int) old('category_id', $selected) === $c->id)>{{ $c->name['en'] ?? '' }}</option>
        @endforeach
    </select>
    @if ($categories->isEmpty())<small>Add a category first.</small>@endif
</label>
