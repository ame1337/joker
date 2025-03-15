<div class="card mt-3">
    <div class="card-body">
        <h5 class="h-name mb-2 fw-bold">
        <i class="fa-solid fa-key"></i> {{ __('Update password') }}
        </h5>

        <div>
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('put')

                <div class="mb-3">
                    <label for="update_password_current_password" class="form-label">@lang('Current password')</label>
                    <input id="update_password_current_password" type="password"
                        class="form-control rounded-3 @error('current_password', 'updatePassword') is-invalid @enderror" 
                        name="current_password" required autocomplete="current-password">
                    @error('current_password', 'updatePassword')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="update_password_password" class="form-label">@lang('New password')</label>
                    <input id="update_password_password" type="password"
                        class="form-control rounded-3 @error('password', 'updatePassword') is-invalid @enderror" 
                        name="password" required autocomplete="new-password">
                        <small class="form-text">@lang('Min 8 characters')</small>
                    @error('password', 'updatePassword')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="update_password_password_confirmation" class="form-label">@lang('Confirm password')</label>
                    <input id="update_password_password_confirmation" type="password"
                        class="form-control rounded-3 @error('password_confirmation', 'updatePassword') is-invalid @enderror" 
                        name="password_confirmation" required autocomplete="new-password">
                    @error('password_confirmation', 'updatePassword')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-success rounded-3">
                    @lang('Save')
                </button>
            </form>
        </div>
    </div>
</div>