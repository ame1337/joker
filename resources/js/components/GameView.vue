<template>
    <div>
        <div v-if="game === null" class="d-flex justify-content-center align-items-center vh-100">
            <div v-show="this.showPasswordForm">
                    <div class="row">
                        <div class="col-8 position-relative">
                            <input type="text"
                                maxlength="4"
                                :placeholder="lang('Pin code')"
                                class="form-control"
                                :class="errorMessage ? 'is-invalid' : ''"
                                v-model="passwordInput"
                                name="password"
                                :disabled="showLoadingBtn"
                                @keypress="joinWithEnter"
                            >
                            <span v-show="errorMessage" class="invalid-tooltip">
                                <strong v-text="errorMessage"></strong>
                            </span>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-success" @click="join(true)" :disabled="showLoadingBtn">
                                <span class="spinner-border spinner-border-sm" v-show="showLoadingBtn"></span>
                                <span v-show="!showLoadingBtn">{{ lang('Join') }}</span>
                            </button>
                        </div>
                    </div>                    
            </div>
            <div v-show="showLoading" class="alert alert-success rounded-3">
                <div class="spinner-border spinner-border-sm" role="status"></div> {{ lang('Loading') }}...
            </div>
        </div>

        <game v-else :initial-game="game"
            :initial-cards="cards" 
            :is_bot_disabled="is_bot_disabled"
            :init_bot_timer="init_bot_timer"
            :ka="ka">
        </game>
    </div>
</template>

<script>
    import translate from '../mixins/translate';
    import game from './Game.vue';

    export default {
        components: { game },
        props: ['gameId', 'hasPassword', 'pinCode', 'is_bot_disabled', 'init_bot_timer'],
        mixins: [translate],

        mounted() {
            if (this.hasPassword === false) {
                this.join();
            } else if (this.pinCode) {
                this.join(true);
            }
        },

        data() {
            return {
                game: null,
                cards: null,
                passwordInput: this.pinCode,
                errorMessage: '',
                showPasswordForm: this.hasPassword,
                showLoading: false,
                showLoadingBtn: false
            }
        },

        methods: {
            join(btnPressed = false) {
                let password = null;

                if (! btnPressed) {
                    this.showLoading = true;
                } else {
                    if (! this.passwordInput) {
                        this.errorMessage = this.lang('Enter pin code');
                        return;
                    } else {
                        this.errorMessage = '';
                        this.showLoadingBtn = true;
                        password = { pin: this.passwordInput }
                    }
                }

                axios.post('/join/games/' + this.gameId, password)
                    .then(response => {
                        this.game = response.data.game;
                        this.cards = response.data.cards;
                        this.showLoading = false;
                    })
                    .catch(error => {
                        console.log(error)
                        if (error.response.data.errors) {
                            this.passwordInput = '';
                            this.showLoadingBtn = false;
                            this.errorMessage = this.lang('Invalid pin code');
                        } else {
                            window.location = '/lobby';
                        }
                    });
            },

            joinWithEnter(event)
            {
                if (event.key === 'Enter') {
                    this.join(true);
                }
            }
        }
    }
</script>
