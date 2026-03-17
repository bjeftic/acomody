@extends('layouts.superadmin')

@section('content')
<section>
    <div class="panel panel-default">
        <div class="panel-heading">Feature Flags</div>

        <div class="panel-body">
            <a href="{{ route('admin.feature-flags.create') }}" class="btn btn-default">Add Feature Flag</a>

            @if(Session::has('success'))
                <p class="alert alert-success" style="margin-top: 10px;">{{ Session::get('success') }}</p>
            @endif

            <table class="table table-hover table-striped table-condensed" style="margin-top: 10px;">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Key</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Updated</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($featureFlags as $flag)
                    <tr>
                        <td>{{ $flag->id }}</td>
                        <td><code>{{ $flag->key }}</code></td>
                        <td>{{ $flag->name }}</td>
                        <td>{{ $flag->description ?? '—' }}</td>
                        <td>
                            @if($flag->is_enabled)
                                <span class="label label-success">Enabled</span>
                            @else
                                <span class="label label-default">Disabled</span>
                            @endif
                        </td>
                        <td>{{ $flag->updated_at->format('Y-m-d H:i') }}</td>
                        <td align="right">
                            <div class="btn-group">
                                <form method="POST" action="{{ route('admin.feature-flags.toggle', $flag) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-xs {{ $flag->is_enabled ? 'btn-warning' : 'btn-success' }}">
                                        {{ $flag->is_enabled ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>
                                <a href="{{ route('admin.feature-flags.edit', $flag) }}" class="btn btn-default btn-xs">Edit</a>
                                <form method="POST" action="{{ route('admin.feature-flags.destroy', $flag) }}"
                                      style="display:inline;"
                                      onsubmit="return confirm('Delete feature flag \'{{ $flag->key }}\'? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-xs">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">No feature flags yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
