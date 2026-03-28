@php $prevDate = null; @endphp
<div class="timeline" style="position:relative; padding-left:24px; border-left:3px solid #ddd;">
    @forelse($logs as $log)
        @php $curDate = $log->created_at->toDateString(); @endphp
        @if($curDate !== $prevDate)
            @php $prevDate = $curDate; @endphp
            <div style="margin:-8px 0 12px -34px; font-weight:bold; color:#777; font-size:12px; text-transform:uppercase; letter-spacing:1px;">
                {{ $log->created_at->format('l, d M Y') }}
            </div>
        @endif

        <div style="position:relative; margin-bottom:18px;">
            {{-- Dot --}}
            <span style="position:absolute; left:-31px; top:3px; width:14px; height:14px; border-radius:50%; background:#fff; border:3px solid
                @if(str_starts_with($log->event->value, 'booking.')) #337ab7
                @elseif(str_starts_with($log->event->value, 'payment.')) #5cb85c
                @elseif(str_starts_with($log->event->value, 'user.') || str_starts_with($log->event->value, 'host.')) #5bc0de
                @elseif(str_starts_with($log->event->value, 'accommodation.')) #f0ad4e
                @else #aaa
                @endif
            ; display:inline-block;"></span>

            <div style="display:flex; align-items:flex-start; gap:10px;">
                <div style="flex:1;">
                    <span class="label {{ $log->event->badgeClass() }}" style="font-size:11px;">
                        {{ $log->event->icon() }} {{ $log->event->label() }}
                    </span>
                    <span style="margin-left:6px;">{{ $log->description }}</span>

                    <div style="margin-top:3px; font-size:12px; color:#888;">
                        @if($log->causer instanceof \App\Models\User)
                            by <a href="{{ url('/admin/users/'.$log->causer->id) }}">{{ $log->causer->email }}</a>
                        @endif

                        @if($log->subject instanceof \App\Models\Booking)
                            &nbsp;·&nbsp; booking <code>{{ $log->subject->id }}</code>
                        @elseif($log->subject instanceof \App\Models\EmailLog)
                            &nbsp;·&nbsp; <a href="{{ route('admin.email-logs.show', $log->subject) }}">view email</a>
                        @endif

                        @if($log->properties)
                            &nbsp;·&nbsp;
                            <a href="#" onclick="this.nextElementSibling.style.display=this.nextElementSibling.style.display==='none'?'inline':'none'; return false;" style="font-size:11px;">details</a>
                            <code style="display:none; font-size:11px; background:#f5f5f5; padding:2px 6px; border-radius:3px; white-space:pre;">{{ json_encode($log->properties, JSON_PRETTY_PRINT) }}</code>
                        @endif
                    </div>
                </div>

                <div style="white-space:nowrap; font-size:12px; color:#aaa; min-width:75px; text-align:right;">
                    {{ $log->created_at->format('H:i:s') }}
                </div>
            </div>
        </div>
    @empty
        <p class="text-muted">No activity yet.</p>
    @endforelse
</div>
