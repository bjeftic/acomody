@extends('layouts.superadmin')

@section('content')
<section>
    <div class="panel panel-default">
        <div class="panel-heading">Activity Timeline</div>

        <div class="panel-body">

            {{-- Filters --}}
            <form method="GET" action="{{ route('admin.activity-logs.index') }}" style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px;">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    style="max-width:240px;"
                    placeholder="Search description..."
                    value="{{ $search }}"
                />

                <select name="event" class="form-control" style="max-width:200px;">
                    <option value="">All events</option>
                    @foreach($eventGroups as $group => $groupEvents)
                        <optgroup label="{{ $group }}">
                            @foreach($groupEvents as $e)
                                <option value="{{ $e->value }}" {{ $event === $e->value ? 'selected' : '' }}>
                                    {{ $e->label() }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>

                <input
                    type="text"
                    name="user_id"
                    class="form-control"
                    style="max-width:120px;"
                    placeholder="User ID..."
                    value="{{ $userId }}"
                />

                <input
                    type="date"
                    name="date_from"
                    class="form-control"
                    style="max-width:150px;"
                    value="{{ $dateFrom }}"
                    title="From date"
                />
                <input
                    type="date"
                    name="date_to"
                    class="form-control"
                    style="max-width:150px;"
                    value="{{ $dateTo }}"
                    title="To date"
                />

                <button type="submit" class="btn btn-primary">Filter</button>
                @if($search || $event || $userId || $dateFrom || $dateTo)
                    <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-default">Clear</a>
                @endif
            </form>

            {{-- Stats --}}
            <div style="margin-bottom:12px;">
                <span class="label label-default" style="font-size:13px; padding:5px 10px;">
                    Total: {{ $logs->total() }}
                </span>
            </div>

            @include('super-admin.activity-logs._timeline', ['logs' => $logs])

            {!! $logs->render() !!}
        </div>
    </div>
</section>
@endsection
