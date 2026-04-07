<div class="col-md-12">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="slug">Slug <span class="text-danger">*</span></label>
                <input type="text"
                       name="slug"
                       id="slug"
                       value="{{ old('slug', $amenity->slug ?? '') }}"
                       class="form-control"
                       placeholder="e.g. wifi, air-conditioning"
                       required>
                <p class="help-block">Lowercase letters, numbers, hyphens and underscores only.</p>
                @error('slug')
                    <span class="help-block text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="icon">Icon (Lucide name)</label>
                <input type="text"
                       name="icon"
                       id="icon"
                       value="{{ old('icon', $amenity->icon ?? '') }}"
                       class="form-control"
                       placeholder="e.g. Wifi, AirVent">
                <p class="help-block">PascalCase Lucide icon name.</p>
                @error('icon')
                    <span class="help-block text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="sort_order">Sort Order</label>
                <input type="number"
                       name="sort_order"
                       id="sort_order"
                       value="{{ old('sort_order', $amenity->sort_order ?? 0) }}"
                       class="form-control"
                       min="0">
                @error('sort_order')
                    <span class="help-block text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="category">Category <span class="text-danger">*</span></label>
        <select name="category" id="category" class="form-control" required>
            <option value="">— Select category —</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ old('category', $amenity->category ?? '') === $cat ? 'selected' : '' }}>
                    {{ ucwords(str_replace('-', ' ', $cat)) }}
                </option>
            @endforeach
        </select>
        @error('category')
            <span class="help-block text-danger">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label>Name</label>

        <ul class="nav nav-tabs" style="margin-bottom: 10px;">
            @foreach($locales as $i => $locale)
                <li class="{{ $i === 0 ? 'active' : '' }}">
                    <a href="#name-tab-{{ $locale['code'] }}" data-toggle="tab">
                        {{ $locale['name'] }}
                        @if($locale['code'] === 'en') <span class="text-danger">*</span> @endif
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="tab-content">
            @foreach($locales as $i => $locale)
                <div class="tab-pane {{ $i === 0 ? 'active' : '' }}" id="name-tab-{{ $locale['code'] }}">
                    <input type="text"
                           name="name[{{ $locale['code'] }}]"
                           value="{{ old('name.'.$locale['code'], isset($amenity) ? $amenity->getTranslation('name', $locale['code'], false) : '') }}"
                           class="form-control"
                           placeholder="{{ $locale['name'] }} name"
                           {{ $locale['code'] === 'en' ? 'required' : '' }}>
                    @error('name.'.$locale['code'])
                        <span class="help-block text-danger">{{ $message }}</span>
                    @enderror
                </div>
            @endforeach
        </div>
    </div>

    <h4>Flags</h4>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="is_active" value="1"
                            {{ old('is_active', $amenity->is_active ?? true) ? 'checked' : '' }}>
                        Active
                    </label>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="is_feeable" value="1"
                            {{ old('is_feeable', $amenity->is_feeable ?? false) ? 'checked' : '' }}>
                        Feeable
                    </label>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="is_highlighted" value="1"
                            {{ old('is_highlighted', $amenity->is_highlighted ?? false) ? 'checked' : '' }}>
                        Highlighted
                    </label>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="is_optional" value="1"
                            {{ old('is_optional', $amenity->is_optional ?? true) ? 'checked' : '' }}>
                        Optional
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        {!! html()->submit(isset($amenity) ? 'Save Changes' : 'Create Amenity')->class('btn btn-primary') !!}
        <a href="{{ route('admin.amenities.index') }}" class="btn btn-default">Cancel</a>
    </div>
</div>
