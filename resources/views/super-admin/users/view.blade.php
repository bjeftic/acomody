@extends('layouts.superadmin')
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

@section('content')
  @include('super-admin.partials.modals.delete')
  <section>
    <!-- User details -->
    <div class="panel panel-default">
      <div class="panel-body">
        <div class="flex" style="justify-content:space-between;">
          <div style="font-size:2rem;font-weight:bold;">User details</div>
          <div class="flex" style="gap:1rem;">
            {!! html()->a()
                ->href("/admin/users/{$user->id}/edit")
                ->class('btn btn-primary')
                ->text('Edit user') !!}

            {!! html()->form("/admin/users/{$user->id}", 'DELETE')
                ->class('form-delete m-0')
                ->open() !!}

            {!! html()->button('Delete user')
                ->type('submit')
                ->class('btn btn-danger')
                ->attribute('name', 'delete_modal') !!}

            {!! html()->form()->close() !!}
        </div>
        </div>
        <hr />
        <div class="flex">
          <div class="flex-1"><b>ID</b><br>{{ $user->id }}</div>
          <div class="flex-1"><b>Name</b><br>{{ trim(($user->userProfile->first_name ?? '') . ' ' . ($user->userProfile->last_name ?? '')) ?: '—' }}</div>
          <div class="flex-1"><b>Email</b><br>{{ $user->email }}</div>
          <div class="flex-1"><b>Verified email</b><br>{{ $user->verified_email_at }}</div>
          <div class="flex-1"><b>Created at</b><br>{{ $user->created_at }}</div>
          <div class="flex-1"><b>Updated at</b><br>{{ $user->updated_at }}</div>
        </div>
      </div>
    </div>

    <!-- Activity Timeline -->
    <div class="panel panel-default">
      <div class="panel-heading" style="display:flex; justify-content:space-between; align-items:center;">
        <span>Activity Timeline (last 20 events)</span>
        <a href="{{ route('admin.activity-logs.user', $user) }}" class="btn btn-default btn-xs">View all</a>
      </div>
      <div class="panel-body">
        @include('super-admin.activity-logs._timeline', ['logs' => $activityLogs])
      </div>
    </div>
  </section>
@endsection
