@extends('layouts.superadmin')

@section('content')
<section>
    <div class="panel panel-default">
        <div class="panel-heading">Amenities</div>

        <div class="panel-body">
            <a href="{{ route('admin.amenities.create') }}" class="btn btn-default">Add Amenity</a>

            @if(Session::has('success'))
                <p class="alert alert-success" style="margin-top: 10px;">{{ Session::get('success') }}</p>
            @endif

            @foreach($categories as $category)
                @php $group = $amenities->where('category', $category); @endphp
                @if($group->isNotEmpty())
                    <h4 style="margin-top: 20px; text-transform: capitalize;">{{ str_replace('-', ' ', $category) }}</h4>
                    <table class="table table-hover table-striped table-condensed">
                        <thead>
                        <tr>
                            <th style="width:4%">ID</th>
                            <th style="width:18%">Slug</th>
                            <th style="width:18%">Name (EN)</th>
                            <th style="width:14%">Icon</th>
                            <th style="width:8%">Feeable</th>
                            <th style="width:10%">Highlighted</th>
                            <th style="width:8%">Optional</th>
                            <th style="width:6%">Sort</th>
                            <th style="width:8%">Status</th>
                            <th style="width:6%"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($group as $amenity)
                            <tr>
                                <td>{{ $amenity->id }}</td>
                                <td><code>{{ $amenity->slug }}</code></td>
                                <td>{{ $amenity->getTranslation('name', 'en') }}</td>
                                <td><code>{{ $amenity->icon ?? '—' }}</code></td>
                                <td>
                                    @if($amenity->is_feeable)
                                        <span class="label label-info">Yes</span>
                                    @else
                                        <span class="label label-default">No</span>
                                    @endif
                                </td>
                                <td>
                                    @if($amenity->is_highlighted)
                                        <span class="label label-warning">Yes</span>
                                    @else
                                        <span class="label label-default">No</span>
                                    @endif
                                </td>
                                <td>
                                    @if($amenity->is_optional)
                                        <span class="label label-default">Yes</span>
                                    @else
                                        <span class="label label-danger">No</span>
                                    @endif
                                </td>
                                <td>{{ $amenity->sort_order }}</td>
                                <td>
                                    @if($amenity->is_active)
                                        <span class="label label-success">Active</span>
                                    @else
                                        <span class="label label-default">Inactive</span>
                                    @endif
                                </td>
                                <td align="right">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.amenities.edit', $amenity) }}" class="btn btn-default btn-xs">Edit</a>
                                        <form method="POST" action="{{ route('admin.amenities.destroy', $amenity) }}"
                                              style="display:inline;"
                                              onsubmit="return confirm('Delete amenity \'{{ $amenity->slug }}\'?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-xs">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            @endforeach
        </div>
    </div>
</section>
@endsection
