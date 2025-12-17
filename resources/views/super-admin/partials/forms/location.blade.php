<div class="col-md-12">
    <div class="form-group">
        {!! html()->label('Name', 'name') !!}
        <div class="form-controls">
            {!! html()->text('name', $location->name ?? '')->class('form-control')->required() !!}
        </div>
    </div>

    <div class="form-group">
        {!! html()->label('Location Type', 'location_type') !!}
        <div class="form-controls">
            {!! html()->select('location_type', $locationTypes ?? [], $location->location_type ?? '')->class('form-control') !!}
        </div>
    </div>

    <div class="form-group">
        {!! html()->label('Country', 'country') !!}
        <div class="form-controls">
            {!! html()->select('country', $countries ?? [], $location->country ?? '')->class('form-control') !!}
        </div>
    </div>

    <div class="form-group">
        {!! html()->label('Parent', 'parent') !!}
        <div class="form-controls">
            {!! html()->select('parent', [], old('parent', $location->parent ?? null))
                ->class('form-control select2-ajax')
                ->id('parent-select')
                ->placeholder('Select parent location') !!}
        </div>
    </div>

    <div class="form-group">
        {!! html()->label('Latitude', 'latitude') !!}
        <div class="form-controls">
            {!! html()->text('latitude', $location->latitude ?? '')->class('form-control') !!}
        </div>
    </div>

    <div class="form-group">
        {!! html()->label('Longitude', 'longitude') !!}
        <div class="form-controls">
            {!! html()->text('longitude', $location->longitude ?? '')->class('form-control') !!}
        </div>
    </div>
</div>

<div class="col-md-12">
    <div class="form-group">
        {!! html()->submit('Save Location')->class('btn btn-primary') !!}
        <a href="{{ route('admin.locations.index') }}" class="btn btn-default">Cancel</a>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#parent-select').select2({
            placeholder: 'Search parent location',
            allowClear: true,
            width: '100%',
            ajax: {
                url: '{{ route("admin.locations.search") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.results,
                        pagination: {
                            more: data.pagination.more
                        }
                    };
                },
                cache: true
            },
            minimumInputLength: 2
        });

        @if(isset($location) && $location->parent && $location->parentName)
            var option = new Option('{{ $location->parentName }}', '{{ $location->parent }}', true, true);
            $('#parent-select').append(option).trigger('change');
        @endif
    });
</script>
@endpush
