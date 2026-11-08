<p class="tabs">
    <a href="{{ route($route) }}" @class(['active' => ! $category])>All</a>
    @foreach ($categories as $c)
        <a href="{{ route($route, ['category' => $c->id]) }}" @class(['active' => $category === $c->id])>{{ $c->name['en'] ?? '' }}</a>
    @endforeach
</p>
