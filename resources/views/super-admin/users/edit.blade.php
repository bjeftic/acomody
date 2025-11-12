@extends('layouts.superadmin')

@section('content')
    <section>
        <div class="panel panel-default">
            <div class="panel-heading">Modify User</div>

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

                @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {!! html()->form('PUT', "/superadmin/users/{$user->id}")
                    ->attribute('onsubmit', 'document.getElementById("configuration").value = JSON.stringify(window.editor.get()); return true;')
                    ->open() !!}

                @include('super-admin.partials.forms.user')

                {!! html()->form()->close() !!}
            </div>
        </div>
    </section>
@endsection
