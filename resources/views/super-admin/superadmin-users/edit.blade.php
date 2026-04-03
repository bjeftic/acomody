@extends('layouts.superadmin')

@section('content')
    <section>
        <div class="panel panel-default">
            <div class="panel-heading">
                Edit Superadmin — {{ $superadmin->email }}
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

                <form method="POST" action="{{ route('admin.superadmin-users.update', $superadmin->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $superadmin->email) }}" required style="max-width:400px;" />
                    </div>

                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="{{ route('admin.superadmin-users.index') }}" class="btn btn-default" style="margin-left:8px;">Cancel</a>
                </form>
            </div>
        </div>
    </section>
@endsection
