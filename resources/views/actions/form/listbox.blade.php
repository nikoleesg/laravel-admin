<div class="form-group">
    <label>{{ $label }}</label>
    <select class="form-control {{$class}}" style="width: 100%;" name="{{$name}}[]" {!! $attributes !!} multiple="multiple" data-placeholder="{{ $label }}">
        @foreach($options as $select => $option)
            <option value="{{$select}}" {{ in_array($select, (array) old($column, $value)) ? 'selected' : '' }}>{{$option}}</option>
        @endforeach
    </select>
    <input type="hidden" name="{{$name}}[]" />
    @include('admin::actions.form.help-block')
</div>
