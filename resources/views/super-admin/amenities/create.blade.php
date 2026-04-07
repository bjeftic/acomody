@extends('layouts.superadmin')

@section('content')
<section>
    <div class="panel panel-default">
        <div class="panel-heading">Add Amenity</div>

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

            <form method="POST" action="{{ route('admin.amenities.store') }}">
                @csrf
                @include('super-admin.partials.forms.amenity')
            </form>
        </div>
    </div>
</section>
@endsection
