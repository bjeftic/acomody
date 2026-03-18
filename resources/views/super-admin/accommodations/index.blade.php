@extends('layouts.superadmin')

@section('content')
<section>
    <div class="panel panel-default">
        <div class="panel-heading">
            Accommodations
        </div>

        <div style="display:flex; padding: 16px;" id="triggerByEnter">
            <input type="text" style="max-width:300px" class="form-control" id="search" value="{{ $search }}" placeholder="Search by title…" />
            <button type="button" class="btn btn-primary mb-2" style="margin-left: 10px;" onclick="doSearch()">Search</button>
        </div>

        <div class="panel-body">
            @foreach (['danger', 'warning', 'success', 'info'] as $msg)
                @if (Session::has('alert-' . $msg))
                    <p class="alert alert-{{ $msg }}">{{ Session::get('alert-' . $msg) }}</p>
                @endif
            @endforeach
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <table class="table table-hover table-striped table-condensed">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Host</th>
                        <th>Location</th>
                        <th>Type</th>
                        <th>Active</th>
                        <th>Created at</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($accommodations as $accommodation)
                        <tr>
                            <td>{{ $accommodation->title }}</td>
                            <td>{{ $accommodation->user->name ?? '-' }}</td>
                            <td>{{ $accommodation->location->name ?? '-' }}</td>
                            <td>{{ $accommodation->accommodation_type }}</td>
                            <td>
                                @if ($accommodation->is_active)
                                    <span class="label label-success">Yes</span>
                                @else
                                    <span class="label label-default">No</span>
                                @endif
                            </td>
                            <td>{{ $accommodation->created_at->format('d M Y') }}</td>
                            <td align="right">
                                <div class="btn-group">
                                    <a href="/admin/accommodations/{{ $accommodation->id }}" class="btn btn-default btn-xs">View</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center; color:#888;">No accommodations found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {!! $accommodations->render() !!}
</section>

<script>
    function doSearch() {
        window.location.assign('/admin/accommodations?page=1&search=' + document.getElementById('search').value);
    }

    document.getElementById('triggerByEnter').onkeyup = function (e) {
        if (e.keyCode === 13) {
            doSearch();
        }
    };
</script>
@endsection
