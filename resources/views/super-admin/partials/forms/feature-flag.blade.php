<div class="col-md-12">
    <div class="form-group">
        <label for="key">Key <span class="text-danger">*</span></label>
        <input type="text"
               name="key"
               id="key"
               value="{{ old('key', $featureFlag->key ?? '') }}"
               class="form-control"
               placeholder="e.g. newBookingFlow"
               required>
        <p class="help-block">Camel case, used in code: <code>config.features.yourKey</code></p>
        @error('key')
            <span class="help-block text-danger">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="name">Name <span class="text-danger">*</span></label>
        <input type="text"
               name="name"
               id="name"
               value="{{ old('name', $featureFlag->name ?? '') }}"
               class="form-control"
               placeholder="e.g. New Booking Flow"
               required>
        @error('name')
            <span class="help-block text-danger">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea name="description"
                  id="description"
                  class="form-control"
                  rows="3"
                  placeholder="Optional description of what this flag controls">{{ old('description', $featureFlag->description ?? '') }}</textarea>
        @error('description')
            <span class="help-block text-danger">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <div class="checkbox">
            <label>
                <input type="checkbox" name="is_enabled" value="1"
                    {{ old('is_enabled', $featureFlag->is_enabled ?? false) ? 'checked' : '' }}>
                Enabled
            </label>
        </div>
    </div>

    <div class="form-group">
        {!! html()->submit(isset($featureFlag) ? 'Save Changes' : 'Create Feature Flag')->class('btn btn-primary') !!}
        <a href="{{ route('admin.feature-flags.index') }}" class="btn btn-default">Cancel</a>
    </div>
</div>
