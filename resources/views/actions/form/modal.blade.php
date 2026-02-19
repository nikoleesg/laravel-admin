<div class="modal" tabindex="-1" role="dialog" id="{{ $modal_id }}">
    <div class="modal-dialog {{ $modal_size }}" role="document">
        <div class="modal-content">
            <div class="modal-header">
                @unless($disable_close)
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                @endunless
                <h4 class="modal-title">{!! $title !!}</h4>
            </div>
            <form>
            <div class="modal-body">
                @foreach($fields as $field)
                    {!! $field->render() !!}
                @endforeach
            </div>
            <div class="modal-footer">
                @unless($disable_close)
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('admin.close') }}</button>
                @endunless
                @unless($disable_submit)
                <button type="submit" class="btn btn-primary">{{ __('admin.submit') }}</button>
                @endunless
            </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->