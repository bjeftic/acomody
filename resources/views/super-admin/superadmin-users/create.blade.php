@extends('layouts.superadmin')

@section('content')
    <section>
        <div class="panel panel-default">
            <div class="panel-heading">
                Create Superadmin User
                <a href="{{ route('admin.superadmin-users.index') }}" class="btn btn-default btn-xs pull-right">← Back</a>
            </div>

            <div class="panel-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <p style="color:#555;">An invitation email will be sent to the address below with a link to set their password.</p>

                <form method="POST" action="{{ route('admin.superadmin-users.store') }}">
                    @csrf

                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required style="max-width:400px;" autofocus />
                    </div>

                    <button type="submit" class="btn btn-success">Create & Send Invitation</button>
                    <a href="{{ route('admin.superadmin-users.index') }}" class="btn btn-default" style="margin-left:8px;">Cancel</a>
                </form>
            </div>
        </div>
    </section>
@endsection
