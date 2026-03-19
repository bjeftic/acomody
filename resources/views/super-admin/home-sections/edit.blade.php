@extends('layouts.superadmin')

@section('content')
<section>
    <div class="panel panel-default">
        <div class="panel-heading">Edit Section: {{ $homeSection->getTranslation('title', 'en') }}</div>

        <div class="panel-body">
            @if(Session::has('success'))
                <p class="alert alert-success">{{ Session::get('success') }}</p>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.home-sections.update', $homeSection) }}">
                @csrf
                @method('PUT')
                @include('super-admin.partials.forms.home-section')
            </form>
        </div>
    </div>

    {{-- Locations Management --}}
    <div class="panel panel-default" style="margin-top: 20px;">
        <div class="panel-heading">Locations in this section</div>
        <div class="panel-body">
            <form method="POST" action="{{ route('admin.home-sections.locations.store', $homeSection) }}" class="form-inline" style="margin-bottom: 16px;">
                @csrf
                <div class="form-group" style="min-width: 350px; margin-right: 10px;">
                    <select name="location_id" id="location-add-select" class="form-control" style="width: 100%;" required>
                        <option value="">Search location...</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Add Location</button>
            </form>

            @if($homeSection->sectionLocations->isNotEmpty())
                <table class="table table-condensed table-hover">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($homeSection->sectionLocations as $sectionLocation)
                            <tr>
                                <td>{{ $sectionLocation->sort_order }}</td>
                                <td>
                                    @if($sectionLocation->location->primaryPhoto?->status === 'completed')
                                        <img src="{{ $sectionLocation->location->primaryPhoto->thumbnail_url }}"
                                             alt="{{ $sectionLocation->location->name }}"
                                             style="width: 50px; height: 40px; object-fit: cover; border-radius: 3px;">
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $sectionLocation->location->getTranslation('name', 'en') }}</td>
                                <td align="right">
                                    <form method="POST"
                                          action="{{ route('admin.home-sections.locations.destroy', [$homeSection, $sectionLocation]) }}"
                                          onsubmit="return confirm('Remove this location from the section?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted">No locations added yet.</p>
            @endif
        </div>
    </div>
</section>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#location-add-select').select2({
            placeholder: 'Search location...',
            allowClear: true,
            width: '100%',
            ajax: {
                url: '{{ route("admin.home-sections.search-locations") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { search: params.term, page: params.page || 1 };
                },
                processResults: function (data) {
                    return { results: data.results, pagination: { more: data.pagination.more } };
                },
                cache: true
            },
            minimumInputLength: 2
        });
    });
</script>
@endpush
@endsection
