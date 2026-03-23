@php
    $locales = config('app.supported_locales', ['en', 'sr', 'de']);
    $localeLabels = ['en' => 'English', 'sr' => 'Serbian', 'de' => 'German'];
@endphp

<div class="col-md-12">
    {{-- Document type --}}
    <div class="form-group">
        <label>Document Type</label>
        <select name="type" class="form-control" style="max-width: 300px;">
            @foreach($documentTypes as $value => $label)
                <option value="{{ $value }}" {{ old('type', $type->value ?? '') === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Title translations --}}
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

        <div class="tab-content" style="margin-bottom: 8px;">
            @foreach($locales as $i => $locale)
                <div class="tab-pane {{ $i === 0 ? 'active' : '' }}" id="title-tab-{{ $locale }}">
                    <input
                        type="text"
                        name="title[{{ $locale }}]"
                        value="{{ old("title.$locale", '') }}"
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

    <hr>

    {{-- Sections --}}
    <h4>Sections <small class="text-muted">Add headings and paragraphs in order</small></h4>

    <div id="sections-container">
        {{-- Existing sections (on old() repopulation) --}}
        @foreach(old('sections', []) as $index => $section)
            @include('super-admin.partials.forms.legal-document-section', [
                'index' => $index,
                'sectionType' => $section['section_type'] ?? 'paragraph',
                'contentValues' => $section['content'] ?? [],
            ])
        @endforeach
    </div>

    <div style="margin-bottom: 24px;">
        <button type="button" class="btn btn-default btn-sm" id="add-section-btn">
            + Add Section
        </button>
    </div>

    <div class="form-group">
        {!! html()->submit('Save Draft')->class('btn btn-primary') !!}
        <a href="{{ route('admin.legal-documents.index') }}" class="btn btn-default">Cancel</a>
    </div>
</div>

{{-- Section template (hidden, cloned by JS) --}}
<template id="section-template">
    @include('super-admin.partials.forms.legal-document-section', [
        'index' => '__INDEX__',
        'sectionType' => 'paragraph',
        'contentValues' => [],
    ])
</template>

@push('scripts')
<script>
    var sectionCount = {{ count(old('sections', [])) }};

    document.getElementById('add-section-btn').addEventListener('click', function () {
        var template = document.getElementById('section-template').innerHTML;
        var html = template.replace(/__INDEX__/g, sectionCount);
        var container = document.getElementById('sections-container');
        var div = document.createElement('div');
        div.innerHTML = html;
        container.appendChild(div.firstElementChild);
        sectionCount++;
        reindexSections();
    });

    document.getElementById('sections-container').addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-section-btn')) {
            e.target.closest('.section-block').remove();
            reindexSections();
        }
        if (e.target.classList.contains('move-up-btn')) {
            var block = e.target.closest('.section-block');
            var prev = block.previousElementSibling;
            if (prev) {
                block.parentNode.insertBefore(block, prev);
                reindexSections();
            }
        }
        if (e.target.classList.contains('move-down-btn')) {
            var block = e.target.closest('.section-block');
            var next = block.nextElementSibling;
            if (next) {
                block.parentNode.insertBefore(next, block);
                reindexSections();
            }
        }
    });

    function reindexSections() {
        var blocks = document.querySelectorAll('#sections-container .section-block');
        blocks.forEach(function (block, i) {
            block.querySelectorAll('[name]').forEach(function (el) {
                el.name = el.name.replace(/sections\[\d+\]/, 'sections[' + i + ']');
            });
            block.querySelector('.section-number').textContent = '#' + (i + 1);
        });
        sectionCount = blocks.length;
    }
</script>
@endpush
