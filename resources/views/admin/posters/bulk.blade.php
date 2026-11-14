@extends('admin.layout')
@section('title', 'Upload many posters')
@section('content')
    <h1>Upload many posters</h1>
    <form method="post" enctype="multipart/form-data" class="card" action="{{ route('admin.posters.bulk.store') }}">
        @csrf

        @include('admin.partials.category-select', ['selected' => request('category')])

        <label>Posters – choose 1 to {{ \App\Http\Requests\Admin\PosterBulkRequest::MAX_FILES }} pictures (jpg/png/webp, max 3 MB each) *
            <input type="file" name="images[]" accept="image/*" multiple required data-bulk-files>
        </label>

        {{-- One row per chosen picture: preview and its English name (from the file name; edit if needed). --}}
        <div data-bulk-list class="bulk-list"></div>

        <label class="check">
            <input type="checkbox" name="footer_below" value="1" @checked(old('footer_below'))>
            Posters are full of text: put the user's name and contacts in a strip <b>below</b> the picture (all posters)
        </label>
        <label class="check">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> Show in app (all posters)
        </label>
        <input type="hidden" name="sort_order" value="0">

        <button class="btn">Upload all</button>
        <a href="{{ route('admin.posters.index') }}">Cancel</a>
    </form>

    <style>
        .bulk-list { display: grid; gap: 8px; margin: 8px 0 14px; }
        .bulk-row { display: grid; grid-template-columns: 64px 1fr; gap: 10px; align-items: center; }
        .bulk-row img { width: 64px; height: 64px; object-fit: cover; border-radius: 8px; border: 1px solid #e6d3b3; }
        .bulk-row input { width: 100%; }
    </style>
    <script>
        (function () {
            const input = document.querySelector('[data-bulk-files]');
            const list = document.querySelector('[data-bulk-list]');
            const max = {{ \App\Http\Requests\Admin\PosterBulkRequest::MAX_FILES }};
            // "good_morning-01.jpg" -> "Good morning 01"
            const nameOf = (file) => {
                const base = file.name.replace(/\.[^.]+$/, '').replace(/[_-]+/g, ' ').replace(/\s+/g, ' ').trim();
                return (base.charAt(0).toUpperCase() + base.slice(1)).slice(0, 100);
            };
            input.addEventListener('change', () => {
                list.innerHTML = '';
                const files = Array.from(input.files);
                if (files.length > max) {
                    alert('Choose at most ' + max + ' pictures at a time.');
                    input.value = '';
                    return;
                }
                files.forEach((file, i) => {
                    const row = document.createElement('div');
                    row.className = 'bulk-row';
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.alt = '';
                    const label = document.createElement('label');
                    label.textContent = 'English name ' + (i + 1);
                    const title = document.createElement('input');
                    title.type = 'text';
                    title.name = 'titles[' + i + ']';
                    title.maxLength = 100;
                    title.value = nameOf(file);
                    label.appendChild(title);
                    row.append(img, label);
                    list.appendChild(row);
                });
            });
        })();
    </script>
@endsection
