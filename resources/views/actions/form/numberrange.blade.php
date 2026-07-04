<div class="form-group">
    <label>{{ $label }}</label>
    <div style="display: flex; align-items: center;">
        <input type="number" name="{{ $name['start'] }}" value="{{ old($column['start'], $value['start'] ?? null) }}" class="{{ $class['start'] }}" {!! $attributes !!} />

        <span style="margin: 0 10px;">-</span>

        <input type="number" name="{{ $name['end'] }}" value="{{ old($column['end'], $value['end'] ?? null) }}" class="{{ $class['end'] }}" {!! $attributes !!} />
    </div>
    @include('admin::actions.form.help-block')
</div>
