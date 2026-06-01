<div class="btn-group pull-right grid-help-btn" style="margin-right: 10px">
    <button type="button" class="btn btn-sm btn-default" data-toggle="modal" data-target="#{{ $modal_id }}" title="{{ $title }}">
        <i class="fa fa-book"></i><span class="hidden-xs">&nbsp;&nbsp;{{ $title }}</span>
    </button>
</div>

<div class="modal fade" id="{{ $modal_id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-book"></i>&nbsp;&nbsp;{{ $title }}</h4>
            </div>
            <div class="modal-body">
                {!! $content !!}
            </div>
        </div>
    </div>
</div>
