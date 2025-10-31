<a {!! $attributes !!} href="{{ $link }}" target="_blank">
    @if($badge)
        <span class="badge bg-{{ $style }}">{{ $badge }}</span>
    @endif

    <i class="fa fa-{{ $icon }}"></i> {{ $title }}
</a>
<script>
    {!! $script !!}
</script>