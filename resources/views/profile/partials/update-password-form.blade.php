<section>
    <div class="d-flex align-items-center mb-4">
        <div class="stat-icon bg-light-warning text-warning me-3">
            <i class="fa-solid fa-key"></i>
        </div>
        <div>
            <h5 class="mb-0 fw-bold">{{ __('Update Password') }}</h5>
            <p class="text-muted small mb-0">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
        </div>
    </div>

    <form id="passwordUpdateForm" method="post" action="{{ route('password.update') }}" class="row g-3">
        @csrf
        @method('put')

        <div class="col-md-12">
            <label class="form-label" for="current_password">{{ __('Current Password') }} <span class="text-danger">*</span></label>
            <input id="current_password" name="current_password" type="password" class="form-control" autocomplete="current-password">
            <div class="invalid-feedback" id="current_password_error"></div>
        </div>

        <div class="col-md-6">
            <label class="form-label" for="password">{{ __('New Password') }} <span class="text-danger">*</span></label>
            <input id="password" name="password" type="password" class="form-control" autocomplete="new-password">
            <div class="invalid-feedback" id="password_error"></div>
        </div>

        <div class="col-md-6">
            <label class="form-label" for="password_confirmation">{{ __('Confirm Password') }} <span class="text-danger">*</span></label>
            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password">
            <div class="invalid-feedback" id="password_confirmation_error"></div>
        </div>

        <div class="col-12 mt-4">
            <button type="submit" class="btn btn-warning text-white" id="savePasswordBtn">
                <i class="fa-solid fa-lock me-2"></i>{{ __('Update Password') }}
            </button>
        </div>
    </form>
</section>

@push('scripts')
<script>
$('#passwordUpdateForm').on('submit', function(e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $('#savePasswordBtn');
    $form.find('.invalid-feedback').text('').hide();
    $form.find('.form-control').removeClass('is-invalid');
    
    $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Updating...');
    
    ajaxPost($form.attr('action'), $form.serialize(), function(res) {
        showSuccess('Password updated successfully.');
        $form[0].reset();
        $btn.prop('disabled', false).html('<i class="fa-solid fa-lock me-2"></i>{{ __('Update Password') }}');
    }, function(err) {
        $btn.prop('disabled', false).html('<i class="fa-solid fa-lock me-2"></i>{{ __('Update Password') }}');
        if (err.status === 422) {
            const errors = err.responseJSON.errors;
            Object.keys(errors).forEach(key => {
                const $input = $form.find(`[name="${key}"]`);
                const $error = $(`#${key}_error`);
                $input.addClass('is-invalid');
                $error.text(errors[key][0]).show();
            });
        }
    });
});
</script>
<style>
.bg-light-warning { background-color: rgba(255, 193, 7, 0.1); }
.text-warning { color: #ffc107 !important; }
</style>
@endpush
