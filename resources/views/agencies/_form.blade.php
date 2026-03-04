@csrf

<div class="row g-3">
    <div class="col-md-6 mb-3">
        <label for="name" class="form-label">Agency Name <span class="text-danger">*</span></label>
        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
               value="{{ old('name', $agency->name ?? '') }}" placeholder="Enter Agency Name" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="owner_name" class="form-label">Owner Name <span class="text-danger">*</span></label>
        <input type="text" name="owner_name" id="owner_name" class="form-control @error('owner_name') is-invalid @enderror" 
               value="{{ old('owner_name', $agency->owner_name ?? '') }}" placeholder="Enter Owner Name" required>
        @error('owner_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12 mb-3">
        <label for="contact" class="form-label">Contact Number <span class="text-danger">*</span></label>
        <input type="text" name="contact" id="contact" class="form-control @error('contact') is-invalid @enderror" 
               value="{{ old('contact', $agency->contact ?? '') }}" placeholder="Enter Contact" required>
        @error('contact')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12 mb-3">
        <label for="remark" class="form-label">Remark</label>
        <textarea name="remark" id="remark" class="form-control @error('remark') is-invalid @enderror" placeholder="Enter Remark" rows="3">{{ old('remark', $agency->remark ?? '') }}</textarea>
        @error('remark')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mt-4">
    <button type="submit" class="btn btn-primary px-4">
        <i class="fa-solid fa-save me-2"></i>{{ isset($agency) ? 'Update Agency' : 'Create Agency' }}
    </button>
    <a href="{{ route('agencies.index') }}" class="btn btn-light px-4 ms-2">Cancel</a>
</div>
