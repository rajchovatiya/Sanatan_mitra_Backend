@props(['model'])
{{-- Order and on/off switch shared by every content form. --}}
<div class="row">
    <label>Order (smaller shows first)
        <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $model->sort_order ?? 0) }}">
    </label>
    <label class="check">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $model->is_active))> Show in app
    </label>
</div>
