@extends('layouts.superadmin')

@section('content')
<section>
    <div class="panel panel-default">
        <div class="panel-heading">
            {{ $legalDocument->type->label() }} — v{{ $legalDocument->version }}
            @if($legalDocument->is_published)
                <span class="label label-success" style="margin-left: 8px;">Published</span>
            @else
                <span class="label label-default" style="margin-left: 8px;">Draft</span>
            @endif
        </div>

        <div class="panel-body">
            @if(Session::has('success'))
                <p class="alert alert-success">{{ Session::get('success') }}</p>
            @endif

            <div style="margin-bottom: 24px;">
                <a href="{{ route('admin.legal-documents.index') }}" class="btn btn-default btn-sm">← Back</a>
                @if(!$legalDocument->is_published)
                    <form method="POST" action="{{ route('admin.legal-documents.publish', $legalDocument) }}" style="display:inline; margin-left: 8px;">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm"
                            onclick="return confirm('Publish this version? It will become the active document visible to all users.')">
                            Publish this version
                        </button>
                    </form>
                @endif
            </div>

            <table class="table table-condensed" style="max-width: 600px; margin-bottom: 24px;">
                <tr><th style="width: 160px;">Type</th><td>{{ $legalDocument->type->label() }}</td></tr>
                <tr><th>Version</th><td>v{{ $legalDocument->version }}</td></tr>
                <tr><th>Status</th><td>{{ $legalDocument->is_published ? 'Published' : 'Draft' }}</td></tr>
                <tr><th>Published At</th><td>{{ $legalDocument->published_at?->format('Y-m-d H:i') ?? '—' }}</td></tr>
                <tr><th>Created By</th><td>{{ $legalDocument->author->name ?? '—' }}</td></tr>
                <tr><th>Created At</th><td>{{ $legalDocument->created_at->format('Y-m-d H:i') }}</td></tr>
            </table>

            @php
                $locales = config('app.supported_locales', ['en', 'sr', 'de']);
                $localeLabels = ['en' => 'English', 'sr' => 'Serbian', 'de' => 'German'];
            @endphp

            <h4>Title</h4>
            <ul class="nav nav-tabs" style="margin-bottom: 10px;">
                @foreach($locales as $i => $locale)
                    <li class="{{ $i === 0 ? 'active' : '' }}">
                        <a href="#preview-title-{{ $locale }}" data-toggle="tab">
                            {{ $localeLabels[$locale] ?? strtoupper($locale) }}
                        </a>
                    </li>
                @endforeach
            </ul>
            <div class="tab-content" style="margin-bottom: 32px;">
                @foreach($locales as $i => $locale)
                    <div class="tab-pane {{ $i === 0 ? 'active' : '' }}" id="preview-title-{{ $locale }}">
                        <p>{{ $legalDocument->getTranslation('title', $locale) ?: '—' }}</p>
                    </div>
                @endforeach
            </div>

            <h4>Sections ({{ $legalDocument->sections->count() }})</h4>

            @foreach($locales as $i => $locale)
                <ul class="nav nav-tabs" style="margin-bottom: 10px; margin-top: 8px;">
                    @foreach($locales as $j => $l)
                        <li class="{{ $j === 0 ? 'active' : '' }}">
                            <a href="#preview-sections-{{ $l }}" data-toggle="tab">
                                {{ $localeLabels[$l] ?? strtoupper($l) }}
                            </a>
                        </li>
                    @endforeach
                </ul>
                @break
            @endforeach

            <div class="tab-content" style="margin-bottom: 16px;">
                @foreach($locales as $i => $locale)
                    <div class="tab-pane {{ $i === 0 ? 'active' : '' }}" id="preview-sections-{{ $locale }}">
                        @forelse($legalDocument->sections as $section)
                            <div style="margin-bottom: 16px; padding: 12px; background: #f9f9f9; border-left: 3px solid #ddd;">
                                <small class="text-muted">{{ $sectionTypes[$section->section_type->value] ?? $section->section_type->value }} — #{{ $loop->iteration }}</small>
                                @if($section->section_type->value === 'heading')
                                    <h2 style="margin-top: 4px;">{{ $section->getTranslation('content', $locale) ?: '—' }}</h2>
                                @elseif($section->section_type->value === 'subheading')
                                    <h3 style="margin-top: 4px;">{{ $section->getTranslation('content', $locale) ?: '—' }}</h3>
                                @else
                                    <p style="margin-top: 4px; white-space: pre-wrap;">{{ $section->getTranslation('content', $locale) ?: '—' }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted">No sections.</p>
                        @endforelse
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endsection
