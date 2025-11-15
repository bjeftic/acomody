@extends('layouts.superadmin')

@push('styles')
<style>
  .flex { display: flex; }
  .flex-1 { flex: 1; }
  .m-0 { margin: 0; }
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
            {!! Html::ul($errors->all()) !!}
          </div>
        @endif

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

    {{-- Pipelines & Funds --}}
    <div class="panel panel-default">
      <div class="panel-body">
        <div class="flex" style="gap:1rem;">
            {!! Html::a(route('admin.accommodation-drafts.edit', $accommodationDraft->id), 'Edit')
                ->class('btn btn-primary') !!}

            {!! Html::a(url("/admin/accommodation-drafts/{$accommodationDraft->id}/approve"), 'Approve')
                ->class('btn btn-success')
                ->attribute('data-toggle', 'tooltip')
                ->attribute('title', 'You will receive notification once accommodation is created') !!}

            {!! Html::a(url("/admin/accommodation-drafts/{$accommodationDraft->id}/reject"), 'Reject')
                ->class('btn btn-danger') !!}
          </div>
      </div>
    </div>
  </section>
@endsection
