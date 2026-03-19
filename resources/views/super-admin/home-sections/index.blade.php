@extends('layouts.superadmin')

@section('content')
<section>
    <div class="panel panel-default">
        <div class="panel-heading">Home Page Sections</div>

        <div class="panel-body">
            @if(Session::has('success'))
                <p class="alert alert-success">{{ Session::get('success') }}</p>
            @endif
            @foreach (['danger', 'warning', 'info'] as $msg)
                @if(Session::has('alert-' . $msg))
                    <p class="alert alert-{{ $msg }}">{{ Session::get('alert-' . $msg) }}</p>
                @endif
            @endforeach

            <a href="{{ route('admin.home-sections.create') }}" class="btn btn-primary" style="margin-bottom: 16px;">Add Section</a>

            <table class="table table-hover table-striped table-condensed">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Title (EN)</th>
                        <th>Type</th>
                        <th>Countries</th>
                        <th>Locations</th>
                        <th>Active</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sections as $section)
                        <tr>
                            <td>{{ $section->sort_order }}</td>
                            <td>{{ $section->getTranslation('title', 'en') }}</td>
                            <td>{{ $section->type->label() }}</td>
                            <td>
                                @if($section->country_codes)
                                    {{ implode(', ', $section->country_codes) }}
                                @else
                                    <span class="text-muted">All</span>
                                @endif
                            </td>
                            <td>{{ $section->sectionLocations->count() }}</td>
                            <td>
                                @if($section->is_active)
                                    <span class="label label-success">Yes</span>
                                @else
                                    <span class="label label-default">No</span>
                                @endif
                            </td>
                            <td align="right">
                                <div class="btn-group">
                                    <a href="{{ route('admin.home-sections.edit', $section) }}" class="btn btn-default btn-xs">Edit</a>
                                    <form method="POST" action="{{ route('admin.home-sections.destroy', $section) }}"
                                          style="display:inline;"
                                          onsubmit="return confirm('Delete this section? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No sections yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
