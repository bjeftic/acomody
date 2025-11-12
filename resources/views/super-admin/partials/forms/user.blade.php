<div class="col-md-12">
    <div class="form-group">
        {!! html()->label('Name', 'name') !!}
        <div class="form-controls">
            {!! html()->text('name', $user->name ?? '')->class('form-control') !!}
        </div>
    </div>

    <div class="form-group">
        {!! html()->label('E-mail', 'email') !!}
        <div class="form-controls">
            {!! html()->email('email', $user->email ?? '')->class('form-control') !!}
        </div>
    </div>

    @if ($origin !== 'edit')
        <div class="form-group">
            {!! html()->label('Password', 'password') !!}
            <div class="form-controls">
                {!! html()->password('password')->class('form-control') !!}
            </div>
        </div>
        <div class="form-group">
            {!! html()->label('Repeat password', 'password_confirmation') !!}
            <div class="form-controls">
                {!! html()->password('password_confirmation')->class('form-control') !!}
            </div>
        </div>
    @else
        <div class="form-group">
            {!! html()->label('Password (if changing)', 'edit_password') !!}
            <div class="form-controls">
                {!! html()->password('edit_password')->class('form-control') !!}
            </div>
        </div>
        <div class="form-group">
            {!! html()->label('Repeat password (if changing)', 'edit_password_confirmation') !!}
            <div class="form-controls">
                {!! html()->password('edit_password_confirmation')->class('form-control') !!}
            </div>
        </div>
    @endif

    <br/><br/>
</div>

<div class="col-md-12">
    {!! html()->submit('Save User')->class('btn btn-primary') !!}
</div>
