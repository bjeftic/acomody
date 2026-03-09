@php
    $locales = config('app.supported_locales', ['en', 'sr', 'de']);
    $localeLabels = ['en' => 'English', 'sr' => 'Serbian', 'de' => 'German'];
    $existingTranslations = isset($location) ? $location->getTranslations('name') : [];
@endphp

<div class="col-md-12">
    <div class="form-group">
        <label>Name</label>

        <ul class="nav nav-tabs" style="margin-bottom: 10px;">
            @foreach($locales as $i => $locale)
                <li class="{{ $i === 0 ? 'active' : '' }}">
                    <a href="#name-tab-{{ $locale }}" data-toggle="tab">
                        {{ $localeLabels[$locale] ?? strtoupper($locale) }}
                        @if($locale === 'en') <span class="text-danger">*</span> @endif
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="tab-content">
            @foreach($locales as $i => $locale)
                <div class="tab-pane {{ $i === 0 ? 'active' : '' }}" id="name-tab-{{ $locale }}">
                    <input
                        type="text"
                        name="name[{{ $locale }}]"
                        value="{{ old("name.$locale", $existingTranslations[$locale] ?? '') }}"
                        class="form-control"
                        placeholder="{{ $localeLabels[$locale] ?? strtoupper($locale) }} name"
                        {{ $locale === 'en' ? 'required' : '' }}
                    >
                    @error("name.$locale")
                        <span class="help-block text-danger">{{ $message }}</span>
                    @enderror
                </div>
            @endforeach
        </div>
    </div>

    <div class="form-group">
        {!! html()->label('Location Type', 'location_type') !!}
        <div class="form-controls">
            {!! html()->select('location_type', $locationTypes ?? [], old('location_type', $location->location_type?->value ?? ''))->class('form-control') !!}
        </div>
    </div>

    <div class="form-group">
        {!! html()->label('Country', 'country') !!}
        <div class="form-controls">
            {!! html()->select('country', $countries ?? [], old('country', $location->country_id ?? ''))->class('form-control') !!}
        </div>
    </div>

    <div class="form-group">
        {!! html()->label('Parent', 'parent') !!}
        <div class="form-controls">
            {!! html()->select('parent', [], old('parent', $location->parent_id ?? null))
                ->class('form-control select2-ajax')
                ->id('parent-select')
                ->placeholder('Select parent location') !!}
        </div>
    </div>

    <div class="form-group">
        {!! html()->label('Latitude', 'latitude') !!}
        <div class="form-controls">
            {!! html()->text('latitude', old('latitude', $location->latitude ?? ''))->class('form-control') !!}
        </div>
    </div>

    <div class="form-group">
        {!! html()->label('Longitude', 'longitude') !!}
        <div class="form-controls">
            {!! html()->text('longitude', old('longitude', $location->longitude ?? ''))->class('form-control') !!}
        </div>
    </div>

    <div class="form-group">
        <div class="checkbox">
            <label>
                <input type="checkbox" name="is_active" value="1"
                    {{ old('is_active', $location->is_active ?? true) ? 'checked' : '' }}>
                Active (visible to users)
            </label>
        </div>
    </div>

    <div class="form-group">
        {!! html()->label('Cover Image', 'image') !!}
        @if(isset($location) && $location->primaryPhoto?->status === 'completed')
            <div style="margin-bottom: 10px;" id="current-image-wrapper">
                <img src="{{ $location->primaryPhoto->medium_url }}"
                     alt="Current cover image"
                     style="max-width: 300px; max-height: 200px; object-fit: cover; border: 1px solid #ddd; border-radius: 4px; display: block; margin-bottom: 8px;">
                <div class="checkbox" style="margin-top: 4px;">
                    <label class="text-danger">
                        <input type="checkbox" name="remove_image" value="1" id="remove-image-checkbox">
                        Remove current image
                    </label>
                </div>
            </div>
        @endif
        <div class="form-controls" id="image-upload-field">
            {!! html()->file('image')->class('form-control')->accept('image/jpeg,image/jpg,image/png,image/webp') !!}
            <p class="help-block">Optional. JPEG, PNG or WebP, max 5MB. Will be shown on the home screen.</p>
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
        // Restore active tab if there are validation errors
        var activeTab = localStorage.getItem('location-name-tab');
        if (activeTab) {
            $('#name-tab-' + activeTab).tab('show');
        }
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            var locale = e.target.href.split('name-tab-').pop();
            localStorage.setItem('location-name-tab', locale);
        });

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

        @if(isset($location) && $location->parent_id)
            var option = new Option('{{ $location->parent?->name }}', '{{ $location->parent_id }}', true, true);
            $('#parent-select').append(option).trigger('change');
        @endif
    });
</script>
@endpush
