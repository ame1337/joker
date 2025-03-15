<div class="card my-3">
    <div class="card-body">
        <h5 class="h-name mb-2 fw-bold">
        <i class="fa-solid fa-user-slash"></i> {{ __('Delete account') }}
        </h5>
        <p>
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>

        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirm-user-deletion">
            {{ __('DELETE ACCOUNT') }}
        </button>
        @error('password', 'userDeletion')
        <flash :message="{{ json_encode($message) }}" :type="{{ json_encode('alert-danger') }}"></flash>
        @enderror
        
    </div>
    <div class="modal fade" id="confirm-user-deletion" data-bs-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header fw-bold">
                    {{ __('Are you sure you want to delete your account?') }}
                </div>
                <div class="modal-body">
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                    </p>
                    <form method="POST" action="{{ route('profile.destroy') }}">
                        @csrf
                        @method('delete')

                        <div class="mb-3">
                            <label for="password" class="form-label" class="visually-hidden"></label>
                            <input id="password" type="password" 
                                class="form-control rounded-3" 
                                name="password" placeholder="{{ __('Password') }}" required>
                        </div>
                        <button class="ms-2 btn rounded-3 btn-danger float-end" type="submit">
                            {{ __('DELETE ACCOUNT') }}
                        </button>
                        <button type="button" class="btn btn-secondary float-end" data-bs-dismiss="modal">{{ __('CANCEL') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>