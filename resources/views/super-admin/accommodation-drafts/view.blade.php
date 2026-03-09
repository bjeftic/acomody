@extends('layouts.superadmin')

@push('styles')
<style>
    .detail-label { font-weight: bold; min-width: 140px; display: inline-block; }
    .detail-row { margin-bottom: 6px; }
    .photo-grid { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; }
    .photo-thumb { position: relative; }
    .photo-thumb img { width: 180px; height: 130px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; }
    .photo-primary-badge { position: absolute; top: 4px; left: 4px; background: #16a34a; color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 3px; }
    .comment-item { background: #f5f5f5; border-left: 3px solid #337ab7; padding: 10px 14px; margin-bottom: 10px; border-radius: 3px; }
    .comment-meta { font-size: 12px; color: #888; margin-bottom: 4px; }
    .pill { display: inline-block; border: 1px solid #ccc; padding: 2px 8px; margin: 2px; border-radius: 12px; font-size: 12px; background: #f9f9f9; }
    .section-title { font-size: 16px; font-weight: bold; margin-bottom: 10px; }
    .status-badge { display: inline-block; padding: 3px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; }
    .status-waiting { background: #fff3cd; color: #856404; }
    .status-processing { background: #cce5ff; color: #004085; }
    .status-published { background: #d4edda; color: #155724; }
    .status-rejected { background: #f8d7da; color: #721c24; }
    .status-draft { background: #e2e3e5; color: #383d41; }
</style>
@endpush

@section('content')
@include('super-admin.partials.modals.delete')

<section>

    {{-- Header --}}
    <div class="panel panel-default">
        <div class="panel-body">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h2 style="margin:0;">Accommodation Draft Review</h2>
                <span class="status-badge status-{{ $accommodationDraft->status }}">
                    {{ ucwords(str_replace('_', ' ', $accommodationDraft->status)) }}
                </span>
            </div>
        </div>
    </div>

    {{-- Alerts --}}
    @foreach (['danger', 'warning', 'success', 'info'] as $msg)
        @if (session()->has('alert-' . $msg))
            <div class="alert alert-{{ $msg }}">
                {{ session('alert-' . $msg) }}
            </div>
        @endif
    @endforeach
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Validation errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul style="margin:0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Basic Details --}}
    <div class="panel panel-default">
        <div class="panel-heading"><span class="section-title">Basic Details</span></div>
        <div class="panel-body">
            <div class="detail-row"><span class="detail-label">ID:</span> {{ $accommodationDraft->id }}</div>
            <div class="detail-row"><span class="detail-label">Host:</span>
                {{ $accommodationDraft->user->name ?? '-' }}
                ({{ $accommodationDraft->user->email ?? '-' }})
            </div>
            <div class="detail-row"><span class="detail-label">Type:</span> {{ $accommodationDraft->draftData['accommodation_type'] ?? '-' }}</div>
            <div class="detail-row"><span class="detail-label">Occupation:</span> {{ $accommodationDraft->draftData['accommodation_occupation'] ?? '-' }}</div>
            <div class="detail-row"><span class="detail-label">Title:</span> {{ $accommodationDraft->draftData['title'] ?? '-' }}</div>
            <div class="detail-row"><span class="detail-label">Description:</span> {{ $accommodationDraft->draftData['description'] ?? '-' }}</div>
            <div class="detail-row"><span class="detail-label">Email:</span> {{ $accommodationDraft->draftData['email'] ?? '-' }}</div>
            <div class="detail-row"><span class="detail-label">Website:</span> {{ $accommodationDraft->draftData['website'] ?? '-' }}</div>
            <div class="detail-row"><span class="detail-label">Max Guests:</span> {{ $accommodationDraft->draftData['max_guests'] ?? '-' }}</div>
            <div class="detail-row"><span class="detail-label">Bedrooms:</span> {{ $accommodationDraft->draftData['bedrooms'] ?? '-' }}</div>
            <div class="detail-row"><span class="detail-label">Bathrooms:</span> {{ $accommodationDraft->draftData['bathrooms'] ?? '-' }}</div>
            <div class="detail-row"><span class="detail-label">Submitted:</span> {{ $accommodationDraft->created_at->format('d M Y H:i') }}</div>
        </div>
    </div>

    {{-- Address & Coordinates --}}
    <div class="panel panel-default">
        <div class="panel-heading"><span class="section-title">Address</span></div>
        <div class="panel-body">
            <div class="detail-row"><span class="detail-label">Street:</span> {{ $accommodationDraft->draftData['street'] ?? '-' }}</div>
            <div class="detail-row"><span class="detail-label">City:</span> {{ $accommodationDraft->draftData['city'] ?? '-' }}</div>
            <div class="detail-row"><span class="detail-label">State:</span> {{ $accommodationDraft->draftData['state'] ?? '-' }}</div>
            <div class="detail-row"><span class="detail-label">Country:</span> {{ $accommodationDraft->draftData['country'] ?? '-' }}</div>
            <div class="detail-row"><span class="detail-label">Postal Code:</span> {{ $accommodationDraft->draftData['postal_code'] ?? '-' }}</div>
            @if (!empty($accommodationDraft->draftData['coordinates']))
                <div class="detail-row"><span class="detail-label">Coordinates:</span>
                    {{ $accommodationDraft->draftData['coordinates']['latitude'] ?? '-' }},
                    {{ $accommodationDraft->draftData['coordinates']['longitude'] ?? '-' }}
                </div>
            @endif
        </div>
    </div>

    {{-- House Rules --}}
    <div class="panel panel-default">
        <div class="panel-heading"><span class="section-title">House Rules</span></div>
        <div class="panel-body">
            @forelse ($accommodationDraft->draftData['house_rules'] as $rule => $value)
                <div class="detail-row">
                    <span class="detail-label">{{ ucwords(str_replace('_', ' ', $rule)) }}:</span>
                    {{ is_bool($value) ? ($value ? 'Yes' : 'No') : ($value ?? '-') }}
                </div>
            @empty
                <p style="color:#888;">No house rules provided.</p>
            @endforelse
        </div>
    </div>

    {{-- Pricing --}}
    @if (!empty($accommodationDraft->draftData['pricing']))
    <div class="panel panel-default">
        <div class="panel-heading"><span class="section-title">Pricing</span></div>
        <div class="panel-body">
            @foreach ($accommodationDraft->draftData['pricing'] as $key => $value)
                @if (!is_array($value))
                    <div class="detail-row">
                        <span class="detail-label">{{ ucwords(str_replace(['_', 'Price', 'Nights'], [' ', ' Price', ' Nights'], preg_replace('/([A-Z])/', ' $1', $key))) }}:</span>
                        {{ $value ?? '-' }}
                    </div>
                @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- Amenities --}}
    @if (!empty($accommodationDraft->draftData['amenities']))
    <div class="panel panel-default">
        <div class="panel-heading"><span class="section-title">Amenities</span></div>
        <div class="panel-body">
            @foreach ($accommodationDraft->draftData['amenities'] as $amenity)
                <span class="pill">{{ is_array($amenity) ? ($amenity['name'] ?? $amenity['id'] ?? '') : $amenity }}</span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Photos --}}
    <div class="panel panel-default">
        <div class="panel-heading"><span class="section-title">Photos ({{ $accommodationDraft->photos->count() }})</span></div>
        <div class="panel-body">
            @if ($accommodationDraft->photos->isEmpty())
                <p style="color:#888;">No photos uploaded.</p>
            @else
                <div class="photo-grid">
                    @foreach ($accommodationDraft->photos as $photo)
                        <div class="photo-thumb">
                            <img src="{{ $photo->medium_url ?? $photo->url }}" alt="{{ $photo->alt_text ?? 'Photo' }}" />
                            @if ($photo->is_primary)
                                <span class="photo-primary-badge">Primary</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Review Comments --}}
    <div class="panel panel-default">
        <div class="panel-heading"><span class="section-title">Review Comments ({{ $accommodationDraft->reviewComments->count() }})</span></div>
        <div class="panel-body">
            @forelse ($accommodationDraft->reviewComments as $comment)
                <div class="comment-item">
                    <div class="comment-meta">
                        {{ $comment->user->name ?? 'Admin' }} &mdash; {{ $comment->created_at->format('d M Y H:i') }}
                    </div>
                    <div>{{ $comment->body }}</div>
                </div>
            @empty
                <p style="color:#888;">No comments yet.</p>
            @endforelse

            {{-- Add Comment Form --}}
            <hr />
            <p><strong>Add a comment</strong> <small style="color:#888;">(an email will be sent to the host)</small></p>
            {!! html()->form('POST', route('admin.accommodation-drafts.comments.store', $accommodationDraft->id))->open() !!}
                @csrf
                <div class="form-group">
                    {!! html()->textarea('body', old('body'))
                        ->class('form-control')
                        ->attribute('rows', 3)
                        ->placeholder('Write your comment here…') !!}
                </div>
                {!! html()->button('Add Comment', 'submit')->class('btn btn-primary') !!}
            {!! html()->form()->close() !!}
        </div>
    </div>

    {{-- Actions --}}
    @if (in_array($accommodationDraft->status, ['waiting_for_approval', 'draft']))
    <div class="panel panel-default">
        <div class="panel-heading"><span class="section-title">Actions</span></div>
        <div class="panel-body">

            {{-- Approve --}}
            <div class="panel panel-success">
                <div class="panel-heading">Approve</div>
                <div class="panel-body">
                    {!! html()->form('POST', route('admin.accommodation-drafts.approve', $accommodationDraft->id))->open() !!}
                        @csrf
                        <div class="form-group">
                            {!! html()->label('Location', 'location') !!}
                            {!! html()->select('location', [], null)
                                ->class('form-control select2-ajax')
                                ->id('location-select')
                                ->placeholder('Search and select a location') !!}
                            {!! html()->hidden('location_id')->id('location_id_input') !!}
                        </div>
                        {!! html()->button('Approve & Publish', 'submit')
                            ->class('btn btn-success')
                            ->attribute('data-toggle', 'tooltip')
                            ->attribute('title', 'The host will be notified once the accommodation is created') !!}
                    {!! html()->form()->close() !!}
                </div>
            </div>

            {{-- Reject --}}
            <div class="panel panel-danger">
                <div class="panel-heading">Reject</div>
                <div class="panel-body">
                    {!! html()->form('POST', route('admin.accommodation-drafts.reject', $accommodationDraft->id))->open() !!}
                        @csrf
                        <div class="form-group">
                            {!! html()->label('Reason (optional — will be included in the notification email)', 'reason') !!}
                            {!! html()->textarea('reason', old('reason'))
                                ->class('form-control')
                                ->attribute('rows', 3)
                                ->placeholder('Explain why this submission is being rejected…') !!}
                        </div>
                        {!! html()->button('Reject Submission', 'submit')
                            ->class('btn btn-danger')
                            ->attribute('onclick', "return confirm('Are you sure you want to reject this accommodation draft?')") !!}
                    {!! html()->form()->close() !!}
                </div>
            </div>

        </div>
    </div>
    @endif

</section>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#location-select').select2({
            placeholder: 'Search location',
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
                        pagination: { more: data.pagination.more }
                    };
                },
                cache: true
            },
            minimumInputLength: 2
        });

        $('#location-select').on('change', function() {
            $('#location_id_input').val($(this).val());
        });
    });
</script>
@endpush
