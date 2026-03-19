@php
    $locales = config('app.supported_locales', ['en', 'sr', 'de']);
    $localeLabels = ['en' => 'English', 'sr' => 'Serbian', 'de' => 'German'];
    $existingTranslations = isset($homeSection) ? $homeSection->getTranslations('title') : [];
@endphp

<div class="col-md-12">
    <div class="form-group">
        <label>Title</label>

        <ul class="nav nav-tabs" style="margin-bottom: 10px;">
            @foreach($locales as $i => $locale)
                <li class="{{ $i === 0 ? 'active' : '' }}">
                    <a href="#title-tab-{{ $locale }}" data-toggle="tab">
                        {{ $localeLabels[$locale] ?? strtoupper($locale) }}
                        @if($locale === 'en') <span class="text-danger">*</span> @endif
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="tab-content">
            @foreach($locales as $i => $locale)
                <div class="tab-pane {{ $i === 0 ? 'active' : '' }}" id="title-tab-{{ $locale }}">
                    <input
                        type="text"
                        name="title[{{ $locale }}]"
                        value="{{ old("title.$locale", $existingTranslations[$locale] ?? '') }}"
                        class="form-control"
                        placeholder="{{ $localeLabels[$locale] ?? strtoupper($locale) }} title"
                        {{ $locale === 'en' ? 'required' : '' }}
                    >
                    @error("title.$locale")
                        <span class="help-block text-danger">{{ $message }}</span>
                    @enderror
                </div>
            @endforeach
        </div>
    </div>

    <div class="form-group">
        {!! html()->label('Section Type', 'type') !!}
        <div class="form-controls">
            {!! html()->select('type', $sectionTypes, old('type', $homeSection->type?->value ?? ''))->class('form-control') !!}
        </div>
    </div>

    <div class="form-group">
        {!! html()->label('Sort Order', 'sort_order') !!}
        <div class="form-controls">
            <input type="number" name="sort_order" value="{{ old('sort_order', $homeSection->sort_order ?? 0) }}" class="form-control" min="0">
        </div>
        <p class="help-block">Lower number = displayed first on the home page.</p>
    </div>

    <div class="form-group">
        <label>Target Countries</label>
        <select name="country_codes[]" id="country-codes-select" class="form-control" multiple style="height: auto;">
            @foreach($countries as $code => $name)
                <option value="{{ $code }}"
                    {{ in_array($code, old('country_codes', $homeSection->country_codes ?? [])) ? 'selected' : '' }}>
                    {{ $name }} ({{ $code }})
                </option>
            @endforeach
        </select>
        <p class="help-block">Leave empty to show to all visitors. Select one or more countries to restrict visibility.</p>
    </div>

    <div class="form-group">
        <div class="checkbox">
            <label>
                <input type="checkbox" name="is_active" value="1"
                    {{ old('is_active', $homeSection->is_active ?? true) ? 'checked' : '' }}>
                Active (visible on home page)
            </label>
        </div>
    </div>
</div>

<div class="col-md-12">
    <div class="form-group">
        {!! html()->submit('Save Section')->class('btn btn-primary') !!}
        <a href="{{ route('admin.home-sections.index') }}" class="btn btn-default">Cancel</a>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        var activeTab = localStorage.getItem('home-section-title-tab');
        if (activeTab) {
            $('#title-tab-' + activeTab).tab('show');
        }
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            var locale = e.target.href.split('title-tab-').pop();
            localStorage.setItem('home-section-title-tab', locale);
        });

        $('#country-codes-select').select2({
            placeholder: 'All countries (no restriction)',
            allowClear: true,
            width: '100%',
        });
    });
</script>
@endpush
