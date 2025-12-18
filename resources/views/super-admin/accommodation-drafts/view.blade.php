@extends('layouts.superadmin')

@push('styles')
<style>
    .flex {
        display: flex;
    }

    .flex-1 {
        flex: 1;
    }

    .m-0 {
        margin: 0;
    }

    .pill {
        display: inline-block;
        border: 2px solid transparent;
        padding: 0.125rem 0.25rem;
        margin: 0.25rem;
        border-radius: 0.5rem;
    }
</style>
@endpush

@section('content')
@include('super-admin.partials.modals.delete')

<section>
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="flex justify-between items-center">
                <div class="flex text-2xl">
                    <b>Accommodation draft details</b>
                </div>
            </div>

            <hr />

            {{-- Alerts --}}
            @foreach (['danger', 'warning', 'success', 'info'] as $msg)
            @if (session()->has('alert-' . $msg))
            <p class="alert alert-{{ $msg }}">
                {{ session('alert-' . $msg) }}
                {!! Html::a('&times;', '#')
                ->class('close')
                ->attribute('data-dismiss', 'alert')
                ->attribute('aria-label', 'close') !!}
            </p>
            @endif
            @endforeach

            {{-- Validation errors --}}
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Accommodation draft details --}}
            <div class="flex">
                <div class="flex-1">
                    <p><b>ID:</b> {{ $accommodationDraft->id }}</p>
                    <p><b>Accommodation type:</b> {{ $accommodationDraft->draftData['accommodation_type'] ?? '-' }}</p>
                    <p><b>Occupation:</b> {{ $accommodationDraft->draftData['accommodation_occupation'] ?? '-' }}</p>
                    <p><b>Title:</b> {{ $accommodationDraft->draftData['title'] }}</p>
                    <p><b>Description:</b> {{ $accommodationDraft->draftData['description'] }}</p>
                    <p><b>Email:</b> {{ $accommodationDraft->draftData['email'] ?? '-' }}</p>
                    <p><b>Street:</b> {{ $accommodationDraft->draftData['street'] ?? '-' }}</p>
                    <p><b>City:</b> {{ $accommodationDraft->draftData['city'] ?? '-' }}</p>
                    <p><b>Country:</b> {{ $accommodationDraft->draftData['country'] ?? '-' }}</p>
                    <p><b>Postal Code:</b> {{ $accommodationDraft->draftData['postal_code'] ?? '-' }}</p>
                    <hr />
                    <p><b>House Rules:</b></p>
                    @foreach ($accommodationDraft->draftData['house_rules'] as $rule => $value)
                    <p><b>{{ ucwords(str_replace('_', ' ', $rule)) }}:</b> {{ $value ? 'Yes' : 'No' }} - {{ $value }}</p>
                    @endforeach
                    <hr />
                    <p><b>Created at:</b> {{ $accommodationDraft->created_at }}</p>
                    <p><b>Updated at:</b> {{ $accommodationDraft->updated_at }}</p>

                    @if (! empty($widgets))
                    <p><b>Widgets</b></p>
                    <ul>
                        @foreach($widgets as $w)
                        <li>
                            {!! Html::a($w['name'], $w['url'])
                            ->attribute('target', '_blank')
                            ->attribute('rel', 'noopener noreferrer') !!}
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Apply location --}}
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="form-group">
                {!! html()->label('Location', 'location') !!}
                <div class="form-controls">
                    {!! html()->select('location', [], old('location', $location->parent ?? null))
                    ->class('form-control select2-ajax')
                    ->id('location-select')
                    ->placeholder('Select location') !!}
                </div>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="flex" style="gap:1rem;">
                {!! Html::a(route('admin.accommodation-drafts.edit', $accommodationDraft->id), 'Edit')
                ->class('btn btn-primary') !!}

                {!! html()->form('POST', url("/admin/accommodation-drafts/{$accommodationDraft->id}/approve"))
                ->style('display:inline-block;')
                ->open() !!}
                    @csrf
                    {!! html()->hidden('location_id')->id('location_id_input') !!}
                    {!! html()->button('Approve', 'submit')
                    ->class('btn btn-success')
                    ->attribute('data-toggle', 'tooltip')
                    ->attribute('title', 'You will receive notification once accommodation is created') !!}
                {!! html()->form()->close() !!}

                {!! html()->form('DELETE', url("/admin/accommodation-drafts/{$accommodationDraft->id}/delete"))
                ->style('display:inline-block;')
                ->open() !!}
                    @csrf
                    @method('DELETE')
                    {!! html()->button('Reject', 'submit')
                    ->class('btn btn-danger')
                    ->attribute('onclick', "return confirm('Are you sure you want to reject this accommodation draft?')") !!}
                {!! html()->form()->close() !!}
            </div>
        </div>
    </div>
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
                        pagination: {
                            more: data.pagination.more
                        }
                    };
                },
                cache: true
            },
            minimumInputLength: 2
        });

        // Update hidden input when location is selected
        $('#location-select').on('change', function() {
            $('#location_id_input').val($(this).val());
        });

        @if(isset($location) && $location->parent && $location->parentName)
            var option = new Option('{{ $location->parentName }}', '{{ $location->parent }}', true, true);
            $('#location-select').append(option).trigger('change');
        @endif
    });
</script>
@endpush
