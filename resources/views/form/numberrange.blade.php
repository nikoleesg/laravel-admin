<div class="{{$viewClass['form-group']}} {!! ($errors->has($errorKey['min'].'min') || $errors->has($errorKey['max'].'max')) ? 'has-error' : ''  !!}">

    <label for="{{$id['min']}}" class="{{$viewClass['label']}} control-label">{{$label}}</label>

    <div class="{{$viewClass['field']}}">

        @include('admin::form.error')

        <div class="row">
            <div class="col-lg-3">
                <div class="input-group">
                    <input type="number" name="{{$name['min']}}" value="{{ old($column['min'], $value['min'] ?? null) }}" class="form-control {{$class['min']}}" autocomplete="off" style="width: 70px; border: none; border-bottom: 1px solid #ccc; outline: none; box-shadow: none; border-radius: 0;" {!! $attributes !!} />
                </div>
            </div>

            <div class="col-lg-3">
                <div class="input-group">
                    <input type="number" name="{{$name['max']}}" value="{{ old($column['max'], $value['max'] ?? null) }}" class="form-control {{$class['max']}}" autocomplete="off" style="width: 70px; border: none; border-bottom: 1px solid #ccc; outline: none; box-shadow: none; border-radius: 0;" {!! $attributes !!} />
                </div>
            </div>
        </div>

        @include('admin::form.help-block')

    </div>
</div>
