<section>
    <div class="d-flex align-items-center mb-4">
        <div class="stat-icon bg-light-danger text-danger me-3">
            <i class="fa-solid fa-user-xmark"></i>
        </div>
        <div>
            <h5 class="mb-0 fw-bold text-danger">{{ __('Delete Account') }}</h5>
            <p class="text-muted small mb-0">{{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}</p>
        </div>
    </div>

    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#confirmUserDeletionModal">
        <i class="fa-solid fa-trash-can me-2"></i>{{ __('Delete Account') }}
    </button>

    <!-- Modal -->
    <div class="modal fade" id="confirmUserDeletionModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form id="deleteAccountForm" method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')
                    
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="deleteModalLabel">{{ __('Are you sure?') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body py-4">
                        <p class="text-muted small mb-4">
                            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                        </p>

                        <div class="mb-3">
                            <label class="form-label" for="delete_password">{{ __('Password') }}</label>
                            <input id="delete_password" name="password" type="password" class="form-control" placeholder="{{ __('Enter your password') }}">
                            <div class="invalid-feedback" id="delete_password_error"></div>
                        </div>
                    </div>
                    
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-danger" id="confirmDeleteBtn">
                            <i class="fa-solid fa-trash-can me-2"></i>{{ __('Delete Account') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
$('#deleteAccountForm').on('submit', function(e) {
    e.preventDefault();
    const $form = $(this);
    const $btn = $('#confirmDeleteBtn');
    $form.find('.invalid-feedback').text('').hide();
    $form.find('.form-control').removeClass('is-invalid');
    
    $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Deleting...');
    
    ajaxPost($form.attr('action'), $form.serialize(), function(res) {
        showSuccess('Account deleted successfully.');
        window.location.href = '{{ route('login') }}';
    }, function(err) {
        $btn.prop('disabled', false).html('<i class="fa-solid fa-trash-can me-2"></i>{{ __('Delete Account') }}');
        if (err.status === 422) {
            const errors = err.responseJSON.errors;
            if (errors.password) {
                const $input = $('#delete_password');
                const $error = $('#delete_password_error');
                $input.addClass('is-invalid');
                $error.text(errors.password[0]).show();
            }
        } else {
            showError('An error occurred during deletion.');
        }
    });
});
</script>
<style>
.bg-light-danger { background-color: rgba(220, 53, 69, 0.1); }
</style>
@endpush
