<section>
    <div class="d-flex align-items-center mb-4">
        <div class="stat-icon bg-light-purple text-purple me-3">
            <i class="fa-solid fa-user-gear"></i>
        </div>
        <div>
            <h5 class="mb-0 fw-bold">{{ __('Profile Information') }}</h5>
            <p class="text-muted small mb-0">{{ __("Update your account's profile information and email address.") }}</p>
        </div>
    </div>

    <form id="profileInfoForm" method="post" action="{{ route('profile.update') }}" class="row g-3">
        @csrf
        @method('patch')

        <div class="col-md-6">
            <label class="form-label" for="profile_name">{{ __('Name') }} <span class="text-danger">*</span></label>
            <input id="profile_name" name="name" type="text" class="form-control" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            <div class="invalid-feedback" id="profile_name_error"></div>
        </div>

        <div class="col-md-6">
            <label class="form-label" for="profile_email">{{ __('Email') }} <span class="text-danger">*</span></label>
            <input id="profile_email" name="email" type="email" class="form-control" value="{{ old('email', $user->email) }}" required autocomplete="username">
            <div class="invalid-feedback" id="profile_email_error"></div>

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="small text-dark">
                        {{ __('Your email address is unverified.') }}
                        <button form="send-verification" class="btn btn-link btn-sm p-0">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 fw-medium small text-success">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="col-12 mt-4">
            <button type="submit" class="btn btn-primary" id="saveProfileBtn">
                <i class="fa-solid fa-save me-2"></i>{{ __('Save Changes') }}
            </button>
        </div>
    </form>
</section>

@push('scripts')
<script>
$('#profileInfoForm').on('submit', function(e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $('#saveProfileBtn');
    $form.find('.invalid-feedback').text('').hide();
    $form.find('.form-control').removeClass('is-invalid');
    
    $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Saving...');
    
    ajaxPost($form.attr('action'), $form.serialize(), function(res) {
        showSuccess('Profile updated successfully.');
        $btn.prop('disabled', false).html('<i class="fa-solid fa-save me-2"></i>{{ __('Save Changes') }}');
    }, function(err) {
        $btn.prop('disabled', false).html('<i class="fa-solid fa-save me-2"></i>{{ __('Save Changes') }}');
        if (err.status === 422) {
            const errors = err.responseJSON.errors;
            Object.keys(errors).forEach(key => {
                const $input = $form.find(`[name="${key}"]`);
                const $error = $(`#profile_${key}_error`);
                $input.addClass('is-invalid');
                $error.text(errors[key][0]).show();
            });
        }
    });
});
</script>
@endpush
