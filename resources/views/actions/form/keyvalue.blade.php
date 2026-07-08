<div class="form-group">
    <label>{{ $label }}</label>
    <table class="table table-hover">
        <thead>
        <tr>
            <th>{{ __('Key') }}</th>
            <th>{{ __('Value') }}</th>
            <th style="width: 75px;"></th>
        </tr>
        </thead>
        <tbody class="kv-{{ $column }}-table">
        @foreach(old("{$column}.keys", ($value ?: [])) as $k => $v)
            <tr>
                <td>
                    <input name="{{ $name }}[keys][]" value="{{ old("{$column}.keys.{$k}", $k) }}" class="form-control" required/>
                </td>
                <td>
                    <input name="{{ $name }}[values][]" value="{{ old("{$column}.values.{$k}", $v) }}" class="form-control"/>
                </td>
                <td>
                    <div class="{{ $column }}-remove btn btn-warning btn-sm pull-right">
                        <i class="fa fa-trash">&nbsp;</i>{{ __('admin.remove') }}
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr>
            <td></td>
            <td></td>
            <td>
                <div class="{{ $column }}-add btn btn-success btn-sm pull-right">
                    <i class="fa fa-save"></i>&nbsp;{{ __('admin.new') }}
                </div>
            </td>
        </tr>
        </tfoot>
    </table>
    <template class="{{ $column }}-tpl">
        <tr>
            <td>
                <input name="{{ $name }}[keys][]" class="form-control" required/>
            </td>
            <td>
                <input name="{{ $name }}[values][]" class="form-control"/>
            </td>
            <td>
                <div class="{{ $column }}-remove btn btn-warning btn-sm pull-right">
                    <i class="fa fa-trash">&nbsp;</i>{{ __('admin.remove') }}
                </div>
            </td>
        </tr>
    </template>
    @include('admin::actions.form.help-block')
</div>
