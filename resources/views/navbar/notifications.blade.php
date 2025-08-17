<style>
    /*
     * The slimScroll script creates a wrapper called .slimScrollDiv
     * and applies a fixed height to it. We must override it.
     */
    .notifications-menu .slimScrollDiv {
        height: auto !important;
    }

    /*
     * The script ALSO applies a fixed height to the inner .menu list.
     * We must override that as well.
     */
    .notifications-menu .menu {
        height: auto !important;
    }

    /*
     * Finally, we re-apply a max-height and a native scrollbar to the
     * wrapper for when the notification list is long.
     */
    .notifications-menu .slimScrollDiv {
        max-height: 250px;
        overflow-y: auto !important;
    }
</style>

{{-- Get the notifications --}}
@php
    $notifications = Admin::user()->unreadNotifications;
    $notificationCount = $notifications->count();
@endphp
<li class="dropdown notifications-menu">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
        <i class="fa fa-bell-o"></i>
        @if($notificationCount > 0)
            <span class="label label-warning">{{ $notificationCount }}</span>
        @endif
    </a>
    <ul class="dropdown-menu">
        <li class="header">You have {{ $notificationCount }} notifications</li>
        <li>
            <div class="notification-items">
                <!-- inner menu: contains the actual data -->
                <ul class="menu">
                    {{-- Loop through each notification --}}
                    @forelse($notifications as $notification)
                        <li>
                            <a href="{{ $notification->data['link'] ?? $notification->data['url'] ?? '#'}}">
                                <i class="fa {{ $notification->data['icon'] ?? 'fa-users'}} {{ $notification->data['style'] ?? 'text-aqua'}}"></i> {{ $notification->data['title'] }}
                            </a>
                        </li>
                    @empty
                        <li class="text-center" style="padding: 10px;">No new notifications</li>
                    @endforelse
                </ul>
            </div>

        </li>
        <li class="footer"><a href="{{ url('/notifications') }}">View all</a></li>
    </ul>
</li>
