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
                ->href("/superadmin/users/{$user->id}/edit")
                ->class('btn btn-primary')
                ->text('Edit user') !!}

            {!! html()->form("/superadmin/users/{$user->id}", 'DELETE')
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
          <div class="flex-1"><b>Name</b><br>{{ $user->name }}</div>
          <div class="flex-1"><b>Email</b><br>{{ $user->email }}</div>
          <div class="flex-1"><b>Verified email</b><br>{{ $user->verified_email_at }}</div>
          <div class="flex-1"><b>Created at</b><br>{{ $user->created_at }}</div>
          <div class="flex-1"><b>Updated at</b><br>{{ $user->updated_at }}</div>
        </div>
      </div>
    </div>

    <!-- User client companies details -->
    <div class="panel panel-default">
      <div class="panel-body">

      </div>
    </div>
  </section>
@endsection
