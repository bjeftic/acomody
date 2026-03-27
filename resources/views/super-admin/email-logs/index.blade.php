@extends('layouts.superadmin')

@section('content')
<section>
    <div class="panel panel-default">
        <div class="panel-heading">Email Logs</div>

        <div class="panel-body">

            {{-- Filters --}}
            <form method="GET" action="{{ route('admin.email-logs.index') }}" style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px;">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    style="max-width:300px;"
                    placeholder="Search by email or subject..."
                    value="{{ $search }}"
                />
                <select name="status" class="form-control" style="max-width:160px;">
                    <option value="">All statuses</option>
                    @foreach($statuses as $s)
                        <option value="{{ $s->value }}" {{ $status === $s->value ? 'selected' : '' }}>
                            {{ $s->label() }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
                @if($search || $status)
                    <a href="{{ route('admin.email-logs.index') }}" class="btn btn-default">Clear</a>
                @endif
            </form>

            {{-- Stats bar --}}
            <div style="margin-bottom:12px; display:flex; gap:12px;">
                <span class="label label-default" style="font-size:13px; padding:5px 10px;">
                    Total: {{ $emailLogs->total() }}
                </span>
            </div>

            <table class="table table-hover table-striped table-condensed">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Recipient</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Sent at</th>
                    <th>Created at</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($emailLogs as $log)
                    <tr>
                        <td>{{ $log->id }}</td>
                        <td>
                            {{ $log->recipient_email }}
                            @if($log->recipient_name)
                                <br><small class="text-muted">{{ $log->recipient_name }}</small>
                            @endif
                        </td>
                        <td>{{ $log->subject }}</td>
                        <td>
                            <span class="label {{ $log->status->badgeClass() }}">
                                {{ $log->status->label() }}
                            </span>
                            @if($log->error_message)
                                <br><small class="text-danger" title="{{ $log->error_message }}">
                                    {{ \Illuminate\Support\Str::limit($log->error_message, 60) }}
                                </small>
                            @endif
                        </td>
                        <td>{{ $log->sent_at?->format('Y-m-d H:i:s') ?? '—' }}</td>
                        <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td>
                            <a href="{{ route('admin.email-logs.show', $log) }}" class="btn btn-default btn-xs">Preview</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">No email logs found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            {!! $emailLogs->render() !!}
        </div>
    </div>
</section>
@endsection
