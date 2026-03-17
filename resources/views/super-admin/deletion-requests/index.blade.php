@extends('layouts.superadmin')

@section('content')
<section>
    <div class="panel panel-default">
        <div class="panel-heading">
            Deletion Requests

            <div style="display:inline-block; margin-left: 20px;">
                <a href="?status=pending" class="btn btn-xs {{ $status === 'pending' ? 'btn-primary' : 'btn-default' }}">Pending</a>
                <a href="?status=approved" class="btn btn-xs {{ $status === 'approved' ? 'btn-success' : 'btn-default' }}">Approved</a>
                <a href="?status=rejected" class="btn btn-xs {{ $status === 'rejected' ? 'btn-danger' : 'btn-default' }}">Rejected</a>
            </div>
        </div>

        <div class="panel-body">
            @foreach (['danger', 'warning', 'success', 'info'] as $msg)
                @if(Session::has('alert-' . $msg))
                    <p class="alert alert-{{ $msg }}">
                        {{ Session::get('alert-' . $msg) }}
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    </p>
                @endif
            @endforeach

            <table class="table table-hover table-striped table-condensed">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Type</th>
                        <th>Requested at</th>
                        <th>Processed by</th>
                        <th>Processed at</th>
                        <th>Reason</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($deletionRequests as $request)
                    <tr>
                        <td>{{ $request->id }}</td>
                        <td>
                            {{ $request->user?->first_name }} {{ $request->user?->last_name }}
                            <br><small class="text-muted">ID: {{ $request->user_id }}</small>
                        </td>
                        <td>{{ $request->user?->email }}</td>
                        <td>{{ ucwords(str_replace('_', ' ', $request->type->value)) }}</td>
                        <td>{{ $request->created_at->format('Y-m-d H:i') }}</td>
                        <td>{{ $request->processedBy?->email ?? '—' }}</td>
                        <td>{{ $request->processed_at?->format('Y-m-d H:i') ?? '—' }}</td>
                        <td>{{ $request->reason ?? '—' }}</td>
                        <td align="right">
                            @if($request->isPending())
                                <form method="POST" action="{{ route('admin.deletion-requests.approve', $request->id) }}" style="display:inline;"
                                    onsubmit="return confirm('Approve and permanently delete this host account and all their accommodations?')">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-xs">Approve & Delete</button>
                                </form>

                                <button type="button" class="btn btn-default btn-xs" data-toggle="modal" data-target="#reject-modal-{{ $request->id }}">
                                    Reject
                                </button>

                                {{-- Reject Modal --}}
                                <div class="modal fade" id="reject-modal-{{ $request->id }}" tabindex="-1" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('admin.deletion-requests.reject', $request->id) }}">
                                                @csrf
                                                <div class="modal-header">
                                                    <h4 class="modal-title">Reject Deletion Request #{{ $request->id }}</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label>Reason (optional)</label>
                                                        <textarea name="reason" class="form-control" rows="3" placeholder="Explain why the request is rejected..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-warning">Reject Request</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="label label-{{ $request->status->value === 'approved' ? 'success' : 'danger' }}">
                                    {{ ucfirst($request->status->value) }}
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">No {{ $status }} deletion requests.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

{!! $deletionRequests->appends(['status' => $status])->render() !!}
@endsection
