<div class="card mt-3">
    <div class="card-body">
        <h5 class="h-name mb-2 fw-bold">
        <i class="fa-solid fa-user"></i> {{ __('Update profile') }}
        </h5>

        <div>
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('patch')

                <div class="mb-3">
                    <label for="username" class="form-label">@lang('Username')</label>
                    <input id="username" type="text" 
                        class="form-control rounded-3 @error('username') is-invalid 
                        @enderror" name="username" 
                        value="{{ $user->username }}" required autofocus autocomplete="username">

                    @error('username')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">@lang('Email')</label>
                    <input id="email" type="email" 
                        class="form-control rounded-3 @error('email') is-invalid @enderror"
                        name="email" value="{{ $user->email }}" required autocomplete="email">

                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="form-text">
                        {{ __('Your email address is unverified.') }}

                    <button type="button" class="btn btn-sm btn-link p-0 m-0 align-baseline"
                        onclick="event.preventDefault();document.getElementById('send_verification').submit()">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="form-text">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
                @endif

                <button type="submit" class="btn btn-success rounded-3">
                    @lang('Save')
                </button>
            </form>
            <form class="d-none" method="POST" action="{{ route('verification.send') }}" id="send_verification">
            @csrf
            </form>
        </div>
    </div>
</div>