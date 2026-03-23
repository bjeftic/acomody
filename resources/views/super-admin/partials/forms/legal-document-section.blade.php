@php
    $locales = config('app.supported_locales', ['en', 'sr', 'de']);
    $localeLabels = ['en' => 'English', 'sr' => 'Serbian', 'de' => 'German'];
    $sectionType = $sectionType ?? 'paragraph';
    $contentValues = $contentValues ?? [];
@endphp

<div class="section-block" style="border: 1px solid #ddd; border-radius: 4px; padding: 12px; margin-bottom: 12px; background: #fafafa;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <strong class="section-number">#{{ is_numeric($index) ? $index + 1 : '?' }}</strong>
        <div>
            <button type="button" class="btn btn-default btn-xs move-up-btn">↑</button>
            <button type="button" class="btn btn-default btn-xs move-down-btn">↓</button>
            <button type="button" class="btn btn-danger btn-xs remove-section-btn">Remove</button>
        </div>
    </div>

    <div class="form-group" style="margin-bottom: 8px;">
        <label>Type</label>
        <select name="sections[{{ $index }}][section_type]" class="form-control" style="max-width: 200px;">
            @foreach($sectionTypes as $value => $label)
                <option value="{{ $value }}" {{ $sectionType === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group" style="margin-bottom: 0;">
        <label>Content</label>

        <ul class="nav nav-tabs" style="margin-bottom: 8px;">
            @foreach($locales as $i => $locale)
                <li class="{{ $i === 0 ? 'active' : '' }}">
                    <a href="#section-{{ $index }}-tab-{{ $locale }}" data-toggle="tab">
                        {{ $localeLabels[$locale] ?? strtoupper($locale) }}
                        @if($locale === 'en') <span class="text-danger">*</span> @endif
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="tab-content">
            @foreach($locales as $i => $locale)
                <div class="tab-pane {{ $i === 0 ? 'active' : '' }}" id="section-{{ $index }}-tab-{{ $locale }}">
                    <textarea
                        name="sections[{{ $index }}][content][{{ $locale }}]"
                        class="form-control"
                        rows="4"
                        placeholder="{{ $localeLabels[$locale] ?? strtoupper($locale) }} content"
                        {{ $locale === 'en' ? 'required' : '' }}
                    >{{ $contentValues[$locale] ?? '' }}</textarea>
                </div>
            @endforeach
        </div>
    </div>
</div>
