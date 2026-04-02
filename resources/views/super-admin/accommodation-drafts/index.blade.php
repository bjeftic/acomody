@extends('layouts.superadmin')

@section('content')
    <section>
        <div class="panel panel-default">
            <div class="panel-heading">
              Accommodation Drafts
            </div>

            <div style="display:flex; padding: 16px;" id="triggerByEnter">
              <input type="test" style="max-width:300px" class="form-control" id="search" value="{{$search}}" placeholder="Search by title…" />
              <button type="button" class="btn btn-primary mb-2" style="margin-left: 10px;" onclick="search()">Search</button>
            </div>

            <div class="panel-body">
                @foreach (['danger', 'warning', 'success', 'info'] as $msg)
                    @if(Session::has('alert-' . $msg))
                        <p class="alert alert-{{ $msg }}">{{ Session::get('alert-' . $msg) }} <a href="#"
                                                                                                 class="close"
                                                                                                 data-dismiss="alert"
                                                                                                 aria-label="close">&times;</a>
                        </p>
                    @endif
                @endforeach

                <table class="table table-hover table-striped table-condensed">
                    <thead>
                    <tr>
                        <th>Title</th>
                        <th>Host</th>
                        <th>Location</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>In review</th>
                        <th>Created at</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($accommodationDrafts as $draft)
                        @php
                            $data = is_array($draft->data) ? $draft->data : json_decode($draft->data, true);
                            $city = $data['address']['city'] ?? null;
                            $country = $data['address']['country'] ?? null;
                            $location = implode(', ', array_filter([$city, $country]));
                            $type = $data['accommodation_type'] ?? null;
                            $isLockedByOther = $draft->locked_by_id && $draft->locked_by_id !== Auth::id()
                                && $draft->locked_at
                                && \Illuminate\Support\Carbon::parse($draft->locked_at)->addMinutes(\App\Models\AccommodationDraft::LOCK_DURATION_MINUTES)->isFuture();
                        @endphp
                        <tr>
                            @php $title = $data['title'] ?? '—'; $title = is_array($title) ? ($title['en'] ?? reset($title) ?? '—') : $title; @endphp
                            <td>{{ $title }}</td>
                            <td>{{ $draft->user?->userProfile?->first_name }} {{ $draft->user?->userProfile?->last_name }}</td>
                            <td>{{ $location ?: '—' }}</td>
                            <td>{{ $type ? \App\Enums\Accommodation\AccommodationType::from($type)->label() : '—' }}</td>
                            <td>{{ $draft->status }}</td>
                            <td>
                                @if ($isLockedByOther)
                                    <span style="color:#856404;" title="Locked until {{ \Illuminate\Support\Carbon::parse($draft->locked_at)->addMinutes(\App\Models\AccommodationDraft::LOCK_DURATION_MINUTES)->format('H:i') }}">🔒 In review</span>
                                @elseif ($draft->locked_by_id === Auth::id() && $draft->locked_at && \Illuminate\Support\Carbon::parse($draft->locked_at)->addMinutes(\App\Models\AccommodationDraft::LOCK_DURATION_MINUTES)->isFuture())
                                    <span style="color:#155724;">🔒 You</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $draft->created_at->format('Y-m-d H:i') }}</td>
                            <td align="right">
                                <a href='/admin/accommodation-drafts/{{ $draft->id }}' class="btn btn-default btn-xs {{ $isLockedByOther ? 'disabled' : '' }}">View</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    @if (!$accommodationDrafts instanceof \Illuminate\Database\Eloquent\Collection)
      {!! $accommodationDrafts->render() !!}
    @endif
    <script>
      function loadFrame(src, to, subject) {
        document.getElementById('iframe').src = src
        document.getElementById('to').innerHTML = to
        document.getElementById('subject').innerHTML = subject
      }

      function search() {
        window.location.assign('/admin/accommodation-drafts?page={{$page}}&search=' + document.getElementById('search').value)
      }

      document.getElementById('triggerByEnter').onkeyup = function(e) {
      if (e.keyCode === 13) {
        window.location.assign('/admin/accommodation-drafts?page={{$page}}&search=' + document.getElementById('search').value)
      }
      return true;
      }
    </script>
@endsection

