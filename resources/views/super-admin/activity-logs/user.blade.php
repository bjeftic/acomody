@extends('layouts.superadmin')

@section('content')
<section>
    <div class="panel panel-default">
        <div class="panel-heading">
            Activity Timeline &mdash; <a href="{{ url('/admin/users/'.$user->id) }}">{{ $user->email }}</a>
        </div>
        <div class="panel-body">
            <p>
                <a href="{{ route('admin.activity-logs.index', ['user_id' => $user->id]) }}" class="btn btn-default btn-xs">
                    Open in global timeline
                </a>
            </p>

            @include('super-admin.activity-logs._timeline', ['logs' => $logs])

            {!! $logs->render() !!}
        </div>
    </div>
</section>
@endsection
