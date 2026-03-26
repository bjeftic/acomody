@extends('layouts.superadmin')

@push('styles')
<style>
    .detail-label { font-weight: bold; min-width: 160px; display: inline-block; }
    .detail-row { margin-bottom: 6px; }
    .section-title { font-size: 16px; font-weight: bold; margin-bottom: 10px; }
    .photo-grid { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; }
    .photo-thumb { position: relative; }
    .photo-thumb img { width: 180px; height: 130px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; }
    .photo-primary-badge { position: absolute; top: 4px; left: 4px; background: #16a34a; color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 3px; }
    .pill { display: inline-block; border: 1px solid #ccc; padding: 2px 8px; margin: 2px; border-radius: 12px; font-size: 12px; background: #f9f9f9; }
</style>
@endpush

@section('content')
<section>

    <div class="panel panel-default">
        <div class="panel-body" style="display:flex; justify-content:space-between; align-items:center;">
            <h2 style="margin:0;">{{ $accommodation->title }}</h2>
            <div>
                @if ($accommodation->is_active)
                    <span class="label label-success" style="font-size:13px;">Active</span>
                @else
                    <span class="label label-default" style="font-size:13px;">Inactive</span>
                @endif
                @if ($accommodation->is_featured)
                    <span class="label label-warning" style="font-size:13px; margin-left:4px;">Featured</span>
                @endif
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Basic Details --}}
    <div class="panel panel-default">
        <div class="panel-heading"><span class="section-title">Basic Details</span></div>
        <div class="panel-body">
            <div class="detail-row"><span class="detail-label">ID:</span> {{ $accommodation->id }}</div>
            <div class="detail-row"><span class="detail-label">Host:</span>
                {{ $accommodation->user->name ?? '-' }} ({{ $accommodation->user->email ?? '-' }})
            </div>
            <div class="detail-row"><span class="detail-label">Type:</span> {{ $accommodation->accommodation_type->label() }}</div>
            <div class="detail-row"><span class="detail-label">Occupation:</span> {{ $accommodation->accommodation_occupation->label() }}</div>
            <div class="detail-row"><span class="detail-label">Description:</span> {{ $accommodation->description ?? '-' }}</div>
            <div class="detail-row"><span class="detail-label">Booking type:</span> {{ ucwords(str_replace('_', ' ', $accommodation->booking_type)) }}</div>
            <div class="detail-row"><span class="detail-label">Max guests:</span> {{ $accommodation->max_guests }}</div>
            <div class="detail-row"><span class="detail-label">Bedrooms:</span> {{ $accommodation->bedrooms }}</div>
            <div class="detail-row"><span class="detail-label">Bathrooms:</span> {{ $accommodation->bathrooms }}</div>
            <div class="detail-row"><span class="detail-label">Cancellation:</span> {{ ucwords(str_replace('_', ' ', $accommodation->cancellation_policy ?? '-')) }}</div>
            <div class="detail-row"><span class="detail-label">Views:</span> {{ number_format($accommodation->views_count) }}</div>
            <div class="detail-row"><span class="detail-label">Favorites:</span> {{ number_format($accommodation->favorites_count) }}</div>
            <div class="detail-row"><span class="detail-label">Created:</span> {{ $accommodation->created_at->format('d M Y H:i') }}</div>
        </div>
    </div>

    {{-- Location & Address --}}
    <div class="panel panel-default">
        <div class="panel-heading"><span class="section-title">Location & Address</span></div>
        <div class="panel-body">
            <div class="detail-row"><span class="detail-label">Location:</span> {{ $accommodation->location->name ?? '-' }}</div>
            <div class="detail-row"><span class="detail-label">Street address:</span> {{ $accommodation->street_address ?? '-' }}</div>
            @if ($accommodation->latitude && $accommodation->longitude)
                <div class="detail-row">
                    <span class="detail-label">Coordinates:</span>
                    {{ $accommodation->latitude }}, {{ $accommodation->longitude }}
                </div>
            @endif
        </div>
    </div>

    {{-- House Rules --}}
    <div class="panel panel-default">
        <div class="panel-heading"><span class="section-title">House Rules</span></div>
        <div class="panel-body">
            <div class="detail-row"><span class="detail-label">Check-in from:</span> {{ $accommodation->check_in_from ?? '-' }}</div>
            <div class="detail-row"><span class="detail-label">Check-in until:</span> {{ $accommodation->check_in_until ?? '-' }}</div>
            <div class="detail-row"><span class="detail-label">Check-out until:</span> {{ $accommodation->check_out_until ?? '-' }}</div>
            <div class="detail-row"><span class="detail-label">Quiet hours from:</span> {{ $accommodation->quiet_hours_from ?? '-' }}</div>
            <div class="detail-row"><span class="detail-label">Quiet hours until:</span> {{ $accommodation->quiet_hours_until ?? '-' }}</div>
        </div>
    </div>

    {{-- Pricing --}}
    @if ($accommodation->pricing)
    <div class="panel panel-default">
        <div class="panel-heading"><span class="section-title">Pricing</span></div>
        <div class="panel-body">
            <div class="detail-row">
                <span class="detail-label">Base price:</span>
                {{ number_format($accommodation->pricing->base_price, 2) }}
                {{ $accommodation->pricing->currency->code ?? '' }}
            </div>
            <div class="detail-row">
                <span class="detail-label">Base price (EUR):</span>
                {{ number_format($accommodation->pricing->base_price_eur, 2) }} EUR
            </div>
            <div class="detail-row"><span class="detail-label">Min nights:</span> {{ $accommodation->pricing->min_quantity ?? '-' }}</div>
            <div class="detail-row"><span class="detail-label">Max nights:</span> {{ $accommodation->pricing->max_quantity ?? '-' }}</div>
        </div>
    </div>
    @endif

    {{-- Amenities --}}
    @if ($accommodation->amenities->isNotEmpty())
    <div class="panel panel-default">
        <div class="panel-heading"><span class="section-title">Amenities</span></div>
        <div class="panel-body">
            @foreach ($accommodation->amenities as $amenity)
                <span class="pill">{{ $amenity->name }}</span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Photos --}}
    <div class="panel panel-default">
        <div class="panel-heading"><span class="section-title">Photos ({{ $accommodation->photos->count() }})</span></div>
        <div class="panel-body">
            @if ($accommodation->photos->isEmpty())
                <p style="color:#888;">No photos.</p>
            @else
                <div class="photo-grid">
                    @foreach ($accommodation->photos as $photo)
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

    <div style="margin-bottom: 20px;">
        {!! Html::a(route('admin.accommodations.index'), '&larr; Back to accommodations')->class('btn btn-default') !!}
    </div>

</section>
@endsection
