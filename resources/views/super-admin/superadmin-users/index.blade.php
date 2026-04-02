@extends('layouts.superadmin')

@section('content')
    <section>
        <div class="panel panel-default">
            <div class="panel-heading">
                Superadmin Users
                <a href="{{ route('admin.superadmin-users.create') }}" class="btn btn-success btn-xs pull-right">+ New Superadmin</a>
            </div>

            <div style="display:flex; padding: 16px;" id="triggerByEnter">
                <input type="text" style="max-width:300px" class="form-control" id="search" value="{{ $search }}" placeholder="Search by email…" />
                <button type="button" class="btn btn-primary mb-2" style="margin-left: 10px;" onclick="search()">Search</button>
            </div>

            <div class="panel-body">
                @foreach (['danger', 'warning', 'success', 'info'] as $msg)
                    @if (Session::has('alert-' . $msg))
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
                            <th>E-mail</th>
                            <th>Created at</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($superadmins as $admin)
                            <tr>
                                <td>{{ $admin->id }}</td>
                                <td>{{ $admin->email }}</td>
                                <td>{{ $admin->created_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td align="right">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.superadmin-users.edit', $admin->id) }}" class="btn btn-default btn-xs">Edit email</a>

                                        <form method="POST" action="{{ route('admin.superadmin-users.reset-password', $admin->id) }}" style="display:inline;"
                                            onsubmit="return confirm('Reset password for {{ $admin->email }}? The new password will be shown once.')">
                                            @csrf
                                            <button type="submit" class="btn btn-warning btn-xs">Reset password</button>
                                        </form>

                                        @if ($admin->id !== Auth::id())
                                            <form method="POST" action="{{ route('admin.superadmin-users.destroy', $admin->id) }}" style="display:inline;"
                                                onsubmit="return confirm('Delete superadmin {{ $admin->email }}? This cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-xs">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    @if (!$superadmins instanceof \Illuminate\Database\Eloquent\Collection)
        {!! $superadmins->render() !!}
    @endif

    <script>
        function search() {
            window.location.assign('{{ route('admin.superadmin-users.index') }}?page={{ $page }}&search=' + document.getElementById('search').value);
        }

        document.getElementById('triggerByEnter').onkeyup = function (e) {
            if (e.keyCode === 13) {
                search();
            }
        };
    </script>
@endsection
