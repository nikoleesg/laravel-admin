<div class="{{$viewClass['form-group']}} {!! ($errors->has($errorKey['start'].'start') || $errors->has($errorKey['end'].'end')) ? 'has-error' : ''  !!}">

    <label for="{{$id['start']}}" class="{{$viewClass['label']}} control-label">{{$label}}</label>

    <div class="{{$viewClass['field']}}">

        @include('admin::form.error')

        <div style="display: flex; align-items: center;">
            <input type="number" name="{{$name['start']}}" value="{{ old($column['start'], $value['start'] ?? null) }}" class="form-control {{$class['start']}}" autocomplete="off" style="width: 150px" {!! $attributes !!} />

            <span style="margin: 0 10px;">-</span>

            <input type="number" name="{{$name['end']}}" value="{{ old($column['end'], $value['end'] ?? null) }}" class="form-control {{$class['end']}}" autocomplete="off" style="width: 150px" {!! $attributes !!} />
        </div>

        @include('admin::form.help-block')

    </div>
</div>
