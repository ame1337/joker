<div class="card">
    <div class="card-body">
        <h5 class="h-name mb-2 fw-bold">
            <img src="{{ $user->avatar_url }}"
                class="avatar border rounded-circle"
                alt="avatar"> {{ $user->username }}
        </h5>
        <small>
            @lang('Avatar powered by') 
            <a href="https://gravatar.com" target="_blank" class="link-primary">gravatar.com</a>
        </small>
        <hr>
        <div>
            <i class="fas fa-gamepad text-warning"></i> @lang('Games played'): <strong>{{ $user->player->games_played }}</strong>
        </div>
        <div>
            <i class="fas fa-trophy text-success"></i> @lang('Games won'): <strong>{{ $user->player->games_won }}</strong>
        </div>
    </div>
</div>