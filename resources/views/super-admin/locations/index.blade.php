@extends('layouts.superadmin')

@section('content')
    <section>
        <div class="panel panel-default">
            <div class="panel-heading">
              Users
              @if ($users instanceof \Illuminate\Database\Eloquent\Collection)
                <a href="/superadmin/locations">(paginated locations)</a>
              @else
                <a href="/superadmin/locations/all">(all locations)</a>
              @endif
            </div>

            <div style="display:flex; padding: 16px;" id="triggerByEnter">
              <input type="test" style="max-width:300px" class="form-control" id="search" value="{{$search}}" />
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
                        <th>ID</th>
                        <th>Country</th>
                        <th>Parent</th>
                        <th>Name</th>
                        <th>Created at</th>
                        <th>Updated at</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->country->name }}</td>
                            <td>{{ $user->parent->name ?? '' }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->created_at }}</td>
                            <td>{{ $user->updated_at }}</td>
                            <td align="right">
                                <div class="btn-group">
                                    <a href='/admin/locations/{{ $location->id }}' class="btn btn-default btn-xs">View</a>
                                    <a href='/admin/locations/{{ $location->id }}/edit' class="btn btn-default btn-xs">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    @if (!$users instanceof \Illuminate\Database\Eloquent\Collection)
      {!! $users->render() !!}
    @endif
    <script>
      function loadFrame(src, to, subject) {
        document.getElementById('iframe').src = src
        document.getElementById('to').innerHTML = to
        document.getElementById('subject').innerHTML = subject
      }

      function search() {
        window.location.assign('/superadmin/locations?page={{$page}}&search=' + document.getElementById('search').value)
      }

      document.getElementById('triggerByEnter').onkeyup = function(e) {
      if (e.keyCode === 13) {
        window.location.assign('/superadmin/locations?page={{$page}}&search=' + document.getElementById('search').value)
      }
      return true;
      }
    </script>
@endsection

