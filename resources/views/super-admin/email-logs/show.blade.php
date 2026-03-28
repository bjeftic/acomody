@extends('layouts.superadmin')

@section('content')
<section>
    <div style="margin-bottom:12px;">
        <a href="{{ route('admin.email-logs.index') }}" class="btn btn-default btn-sm">&larr; Back to Email Logs</a>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            Email #{{ $emailLog->id }} &mdash; {{ $emailLog->subject }}
        </div>

        <div class="panel-body">
            <table class="table table-condensed" style="max-width:700px;">
                <tr>
                    <th style="width:140px;">Recipient</th>
                    <td>
                        {{ $emailLog->recipient_email }}
                        @if($emailLog->recipient_name)
                            ({{ $emailLog->recipient_name }})
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Subject</th>
                    <td>{{ $emailLog->subject }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        <span class="label {{ $emailLog->status->badgeClass() }}">
                            {{ $emailLog->status->label() }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Sent at</th>
                    <td>{{ $emailLog->sent_at?->format('Y-m-d H:i:s') ?? '—' }}</td>
                </tr>
                <tr>
                    <th>Created at</th>
                    <td>{{ $emailLog->created_at->format('Y-m-d H:i:s') }}</td>
                </tr>
                @if($emailLog->error_message)
                    <tr>
                        <th class="text-danger">Error</th>
                        <td class="text-danger">{{ $emailLog->error_message }}</td>
                    </tr>
                @endif
            </table>
        </div>
    </div>
</section>
@endsection
