<template>
    <div class="d-flex justify-content-center">
        <component :is="scoreboard"
                   :initial-players="game.players"
                   :initial-scores="game.scores"
                   :penalty="game.penalty"></component>

        <div id="play-table">
            <div id="mute" class="btn-table">
                <button type="button" class="btn btn-outline-secondary" @click="muted = ! muted">
                    <i class="fas" :class="muted ? 'fa-volume-mute' : 'fa-volume-up'"></i>
                </button>
            </div>
            <div class="btn-table" v-show="game.state === 'start'">
                <a href="/lobby" class="btn btn-outline-secondary"><i class="fas fa-arrow-circle-left"></i></a>
            </div>
            <div id="btn-chat" class="btn-table d-xl-none">
                <button type="button" class="btn btn-outline-secondary" @click="showChat">
                    <i class="fas fa-comment"></i>
                </button>
            </div>
            <div id="btn-scoreboard" class="btn-table d-md-none">
                <button type="button" class="btn btn-outline-secondary" @click="showScoreboard">
                    <i class="fas fa-table"></i>
                </button>
            </div>
            <div id="trump-wrapper" v-show="game.trump !== null">
                <div><strong>{{ lang('Trump') }}</strong></div>
                <div id="trump" :class="this.game.trump ? game.trump.suit + game.trump.strength : ''"></div>
            </div>

            <!-- played cards -->
            <div v-for="n in [0,1,2,3]"
                :id="`player${n}card`"
                :class="playedCard(n)"
                data-bs-trigger="manual"
                data-bs-html="true"
                :data-bs-placement="get_card_action_placement(n)"
                :show_action="playedCardAction(n)"
                :style="'z-index:' + cardsZIndex(n)">
            </div>
            
            <!-- last played cards -->
            <!-- some errors pop in console from here. adding a check -->
            <div id="last-cards" class="border rounded shadow" v-show="showLastCards">
                <div class="last-cards-wrapper">
                    <div v-if="lastCards.length === 4 && checkLastCards(lastCards)"
                         v-for="(card, idx) in lastCards"
                         :id="`player${idx}-last-card`"
                         class="p-card"
                         :class="card.suit + card.strength"
                         :style="'z-index:' + card.z">
                    </div>
                </div>
            </div>

            <!-- player0 cards -->
            <div v-for="(card, index) in players[ppm[0]].cards"
                 :class="'p-card p0-card ' + card.suit + card.strength"
                 :style="'margin-left: ' + (getMargin(0) + index * 25) + 'px'"
                 :id="index"
                 :key="index"
                 :data-suit="card.suit"
                 :data-strength="card.strength" @click="actionCard"></div>

            <!-- player1 cards -->
            <div v-for="(index) in players[ppm[1]].cards"
                 class="p-card p1-card card_back card_back_size"
                 :style="'margin-top: ' + (getMargin(1) + (index * marginStep())) + 'px'"></div>

            <!-- player2 cards -->
            <div v-for="(index) in players[ppm[2]].cards"
                 class="p-card p2-card card_back card_back_size"
                 :style="'margin-left: ' + (getMargin(2) + (index * marginStep())) + 'px'"></div>

            <!-- player3 cards -->
            <div v-for="(index) in players[ppm[3]].cards"
                 class="p-card p3-card card_back card_back_size"
                 :style="'margin-top: ' + (getMargin(3) + (index * marginStep())) + 'px'"></div>

            <!-- end players cards -->

            <!-- taken cards-->
            <div class="p0-tc taken-card card_back"
                 v-show="game.state === 'card'"
                 v-for="(v, index) in players[ppm[0]].takenCards"
                 :style="'margin-left: ' + (35 + index * 10) + 'px'"></div>

            <div class="p1-tc taken-card card_back"
                 v-show="game.state === 'card'"
                 v-for="(v, index) in players[ppm[1]].takenCards"
                 :style="'margin-top: ' + (35 + index * 10) + 'px'"></div>

            <div class="p2-tc taken-card card_back"
                 v-show="game.state === 'card'"
                 v-for="(v, index) in players[ppm[2]].takenCards"
                 :style="'margin-right: ' + (35 + index * 10) + 'px'"></div>

            <div class="p3-tc taken-card card_back"
                 v-show="game.state === 'card'"
                 v-for="(v, index) in players[ppm[3]].takenCards"
                 :style="'margin-bottom: ' + (35 + index * 10) + 'px'"></div>

            <!-- players -->
            <div v-show="game.state !== 'finished'" id="player0">
                <img v-if="getAvatarUrl(0)"
                     :src="getAvatarUrl(0)"
                     class="avatar border rounded-circle"
                     :class="active(0)"
                     alt="avatar">
                <div v-else class="avatar border rounded-circle"></div>
                <div class="u-name">
                    <a :href="getProfileLink(0)" v-text="getUsername(0)" class="link-body-emphasis" target="_blank"></a>
                </div>
            </div>
            <div v-show="game.state !== 'finished'"
                v-for="n in 3"
                :id="`player${n}`"
                :data-bs-placement="n === 2 ? 'right' : 'top'"
                data-bs-trigger="manual">
                <div class="kick text-secondary" :title="lang('Kick')" v-show="canKickUser(n)">
                    <i class="fas fa-times" @click="kick(n)"></i>
                </div>
                <img v-if="getAvatarUrl(n)"
                     :src="getAvatarUrl(n)"
                     class="avatar border rounded-circle"
                     :class="active(n)"
                     alt="avatar">
                <div v-else class="avatar border rounded-circle"></div>
                <div class="u-name">
                    <a v-if="getProfileLink(n) != '#'" :href="getProfileLink(n)" 
                        v-text="getUsername(n)" class="link-body-emphasis" target="_blank"></a>
                    <span v-else>#{{ ppm[n]+1 }}</span>
                </div>
            </div>

            <div id="start-btn" v-show="showStart">
                <button class="btn btn-danger btn-block shadow"
                        type="button"
                        @click="start">
                    <small><strong>{{ lang('Start') }}</strong></small>
                </button>
            </div>

            <div id="callboard" class="border rounded shadow pt-1 ps-1 d-none">
                <button class="btn mb-1 me-1"
                        :class="idx === Number(game.to_fill) ? 'btn-success': 'btn-secondary'"
                        v-for="idx in callboard"
                        v-text="idx === 0 ? '-' : idx"
                        :data-value="idx"
                        :disabled="game.except === idx"
                        @click="call"></button>
            </div>

            <div id="ready" class="border rounded p-1 text-center d-none">
                <p id="ready-waiting" class="d-none ">{{ lang('Please wait for other players') }}</p>
                <div id="ready-check" class="d-none mb-3">
                    <p>{{ lang('Are you ready?') }}</p>
                    <div id="d-none ready-buttons">
                        <button class="btn btn-success" data-ready="1" @click="actionReady">{{ lang('Yes') }}</button>
                        <button class="btn btn-danger" data-ready="0" @click="actionReady">{{ lang('No') }}</button>
                    </div>
                </div>
                <p v-text="timer"></p>
                <table class="table table-bordered mt-3 mb-0" style="max-width: 310px;">
                    <thead>
                        <tr class="bg-primary text-white">
                            <th scope="col">{{ this.game.players[0] ? this.game.players[0].username : '#1'}}</th>
                            <th scope="col">{{ this.game.players[1] ? this.game.players[1].username : '#2'}}</th>
                            <th scope="col">{{ this.game.players[2] ? this.game.players[2].username : '#3'}}</th>
                            <th scope="col">{{ this.game.players[3] ? this.game.players[3].username : '#4'}}</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <div id="suits" class="d-none border rounded pt-1 px-1">
                <button class="btn btn-light text-danger mb-1 me-1" data-suit="hearts" @click="actionSuit">&hearts;</button>
                <button class="btn btn-light text-dark mb-1" data-suit="clubs" @click="actionSuit">&clubs;</button>
                <button class="btn btn-light text-danger mb-1 me-1" data-suit="diamonds" @click="actionSuit">&diams;</button>
                <button class="btn btn-light text-dark mb-1" data-suit="spades" @click="actionSuit">&spades;</button>
                <button class="btn btn-light btn-block mb-1 w-100" @click="cancelJok('#suits')"
                        v-show="game.state !== 'trump'"><i class="fas fa-times"></i></button>
                <button class="btn btn-light text-dark btn-block mb-1 w-100" data-suit="bez" @click="actionSuit"
                        v-show="game.state === 'trump'">{{ lang('None') }}</button>
            </div>

            <div id="jokhigh" class="d-none border rounded shadow p-1">
                <button class="btn btn-light me-1" data-action="magali" @click="actionJoker">{{ lang('High') }}</button>
                <button class="btn btn-warning me-1" data-action="caigos" @click="actionJoker">{{ lang('Takero') }}</button>
                <button type="button" class="btn btn-danger" @click="cancelJok('#jokhigh')">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div id="jokjoker" class="d-none border rounded shadow p-1">
                <button class="btn btn-light me-1" data-action="mojokra" @click="actionJoker">{{ lang('Jokero') }}</button>
                <button class="btn btn-warning me-1" data-action="kvevidan" @click="actionJoker">{{ lang('Fold') }}</button>
                <button type="button" class="btn btn-danger" @click="cancelJok('#jokjoker')">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div id="password-card" class="border rounded shadow p-3" v-show="passwordProtected">
                <div class="mb-2">
                    <strong>{{ lang('Pin code') }}: </strong>
                    <span @click="copyLink" class="btn">
                        {{ game.password }} <i class="fa-solid fa-copy"></i>
                    </span>
                </div>

                <input class="form-control" id="table-link" readonly
                       :value="url + '/games/' + game.id + '?pin=' + game.password">
            </div>

            <div id="last-cards-icon"
                 @mouseover="showLastCards = true"
                 @mouseleave="showLastCards = false"
                 v-show="game.state === 'card'">
                <i class="fas fa-history"></i>
            </div>

            <div id="bot-timer" v-show="showBotTimer">
                <i class="fas fa-stopwatch"></i>
                <span v-text="botTimer" :class="botTimer > 5 ? 'text-warning' : 'text-danger'"></span>
            </div>

            <div v-if="game.state === 'finished'" id="game-over" class="d-none">
                <div class="card mt-5">
                    <div class="card-body text-center">
                        <h5 class="alert alert-success"><b>{{ lang('GAME OVER') }}</b></h5>
                        <div v-for="n in [0,1,2,3]" :id="`place-${n}`" class="game-over-card">
                            <h5 :class="n > 1 ? 'text-danger' : 'text-success'">{{ n+1 }} <i class="fas fa-trophy"></i></h5>
                            <img src="/storage/imgs/logo.png" class="avatar border rounded-circle" alt="avatar">
                            <div class="u-name">
                                <a href="#" class="text-dark" target="_blank"></a>
                            </div>
                        </div>
                        <div>
                            <a href="/lobby" class="btn btn-lg btn-primary mt-3 w-100">
                                <i class="fas fa-arrow-circle-left"></i> {{ lang('Lobby') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <chat :messages="messages" :game-id="game.id" @clear-chat="messages = []"></chat>
        <button id="show_kicked" type="button" data-bs-toggle="modal" data-bs-target="#kicked" class="d-none">show_kicked</button>
        <div class="modal fade" id="kicked" data-bs-backdrop="static" data-keyboard="false" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content bg-danger">
                    <div class="modal-body">
                        <i class="fas fa-exclamation-triangle"></i> {{ lang('You have been kicked') }}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-warning" data-bs-dismiss="modal" @click="goToLobby">OK!</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="close-w d-none">
            <button type="button" class="btn btn-outline-secondary" @click="closeW">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- audios -->
        <audio id="notification">
            <source src="/storage/sounds/notification.mp3" type="audio/mpeg">
        </audio>
        <audio id="card-play">
            <source src="/storage/sounds/card-play.mp3" type="audio/mpeg">
        </audio>
        <audio id="timer">
            <source src="/storage/sounds/timer.mp3" type="audio/mpeg">
        </audio>
    </div>
</template>

<script>
    import scoreboard1 from './Scoreboard1.vue';
    import scoreboard9 from './Scoreboard9.vue';
    import chat from './Chat.vue';
    import helpers from '../mixins/helpers';
    import gamechannel from '../mixins/gamechannel';
    import playerchannel from '../mixins/playerchannel';

    export default {
        components: { scoreboard1, scoreboard9, chat },
        mixins: [helpers, gamechannel, playerchannel],
        props: ['initialGame', 'initialCards', 'ka', 'is_bot_disable', 'init_bot_timer'],

        data() {
            return {
                game: this.initialGame,
                playState: true,
                players: [
                    {cards: [], takenCards: []},
                    {cards: [], takenCards: []},
                    {cards: [], takenCards: []},
                    {cards: [], takenCards: []},
                ],
                card: {},
                actions: Object.freeze({"magali": this.lang('High'), "caigos": this.lang("Takero"), "mojokra": this.lang('Jokero'), "kvevidan": this.lang('Fold')}),
                actionsuits: Object.freeze({"hearts": "♥", "clubs": "♣", "diamonds": "♦", "spades": "♠"}),
                messages: [{username: '[system]', message: this.lang('To clear the chat type "clear"')}],
                showLastCards: false,
                botTimer: this.init_bot_timer / 1000,
                showBotTimer: false,
                jokerCardCancelled: false,
                muted: true
            }
        },

        computed: {
            scoreboard() {
                return 'scoreboard' + this.game.type;
            },

            callboard() {
                let max;

                if (this.game.quarter % 2 === 0 || this.game.type === 9) {
                    max = 10;
                } else if (this.game.quarter === 3 && this.game.type === 1) {
                    max = 10 - this.game.hand_count;
                } else {
                    max = this.game.hand_count + 1;
                }

                return Array.from(new Array(max).keys());
            },

            turn() {
                return this.game.turn === this.game.players[this.ppm[0]].position;
            }
        },

        methods: {
            start(event) {
                this.game.state = 'ready';

                $('#ready-waiting').removeClass('d-none');
                $('#ready th').eq(this.ppm[0]).addClass('bg-success');
                $('#ready').removeClass('d-none');
                this.timerFn = setInterval(() => {
                    if (this.timer === 0) {
                        $('#ready').addClass('d-none');
                        clearInterval(this.timerFn);
                        this.timer = 10;
                        $('#ready th').removeClass();
                    }
                    this.timer--;
                }, 1000);

                // post start
                axios.post('/start/games/' + this.game.id)
                    .catch(error => {
                        console.log(error.message);
                    });
            },

            actionReady(event) {
                let ready = event.target.getAttribute('data-ready');
                let color  = ready === '1' ? 'bg-success' : 'bg-danger';

                $('#ready-waiting').removeClass('d-none');
                $('#ready-check').addClass('d-none');
                $('#ready th').eq(this.ppm[0]).addClass(color);

                axios.post('/ready/games/' + this.game.id, {ready});
            },

            actionCard(event) {
                if (! this.turn || this.game.state !== 'card' || 
                ! this.playState || ! this.botTimer > 1) {
                    return;
                }
                // checking state if player can play a card
                this.playState = false;

                let strength = Number(event.target.getAttribute('data-strength'));
                let suit = event.target.getAttribute('data-suit');
                let card = { strength, suit };

                this.cardId = event.target.id;
                this.card.card = card;

                if (strength === 16) {
                    if (this.game.cards.length === 0) {
                        $('#jokhigh').removeClass('d-none');
                    } else {
                        $('#jokjoker').removeClass('d-none');
                    }
                } else {
                    if (! this.canPlay(card)) {
                        this.playState = true;
                        return;
                    }
                    this.afterActionCard(card);
                }
            },

            actionJoker(event) {
                let action = event.target.getAttribute('data-action');
                let card = {
                    strength: this.card.card.strength,
                    suit: this.card.card.suit,
                    action
                }
                this.card.action = action;
                if (action === 'magali' || action === 'caigos') {
                    $('#jokhigh').addClass('d-none');
                    $('#suits').removeClass('d-none');
                } else {
                    $('#jokjoker').addClass('d-none');
                    if (! this.canPlay(card)) {
                        this.playState = true;
                        return;
                    }

                    this.afterActionCard(card);
                }
            },

            actionSuit(event) {
                if (! this.turn) {
                    return;
                }

                let suit = event.target.getAttribute('data-suit');
                if (this.game.state === 'trump') {

                    this.playState = false;
                    this.setTrump = false;
                    this.clearBotTimer();

                    axios.post('/trump/games/' + this.game.id, {trump: suit})
                        .catch(error => {
                            console.log(error.message);
                        });
                } else if (this.game.state === 'card') {
                    let card = {
                        strength: this.card.card.strength,
                        suit: this.card.card.suit,
                        action: this.card.action,
                        actionsuit: suit
                    }
                    this.card.actionsuit = suit;
                    if (! this.canPlay(card)) {
                        this.playState = true;
                        return;
                    }

                    this.afterActionCard(card);
                } else {
                    return;
                }

                $('#suits').addClass('d-none');
            },

            sendCard() {
                axios.post('/card/games/' + this.game.id, this.card)
                    .then(response => {
                        this.card = {};
                        this.cardId = null;
                    })
                    .catch(error => {
                        console.log(error.message);
                    });
            },

            call(event) {

                if (! this.turn || this.game.state !== 'call' || ! this.playState) {
                    return;
                }

                $('#callboard').addClass('d-none');
                this.playState = false;
                this.clearBotTimer();
                this.game.turn = this.game.turn === 3 ? 0 : this.game.turn + 1;

                let call = {
                    call: Number(event.target.getAttribute('data-value'))
                }

                axios.post('/call/games/' + this.game.id, call)
                    .catch(error => {
                        console.log(error.message);
                    });
            },

            afterActionCard(card) {
                this.game.cards.push(card);
                this.game.players[this.ppm[0]].card = card;
                this.players[this.ppm[0]].cards.splice(this.cardId, 1);
                this.lastCardsStorage[0] = Object.create(card);
                this.lastCardsStorage[0].z = this.game.cards.length;

                this.clearBotTimer();
                this.playSound('card-play');

                this.sendCard();
                this.hideCards(this.checkTake());
            },

            setBotTimer(ms = false) {
                if (this.is_bot_disabled) return;
                if (! ms) {
                    ms = this.init_bot_timer;
                } else {
                    this.botTimer = ms / 1000;
                }

                this.showBotTimer = true;
                this.botInterval = setInterval(() => {
                    this.botTimer--;
                    if (this.botTimer === 5) this.playSound('timer');
                    if (this.botTimer === -2) {
                        clearInterval(this.botInterval);
                    }
                }, 1000);

                this.botTimeout = setTimeout(() => {
                    this.playState = false;

                    if (this.game.state === 'trump') $('#suits').addClass('d-none');
                    if (this.game.state === 'call') $('#callboard').addClass('d-none');

                    if (this.game.state === 'card' && this.card.hasOwnProperty('action')){
                        if (! $('#jokhigh').hasClass('d-none')) $('#jokhigh').addClass('d-none');
                        if (! $('#jokjoker').hasClass('d-none')) $('#jokjoker').addClass('d-none');
                        if (! $('#suits').hasClass('d-none')) $('#suits').addClass('d-none');
                    }

                    clearInterval(this.botInterval);
                    this.botTimer = this.init_bot_timer / 1000;
                    this.showBotTimer = false;
                    this.jokerCardCancelled = false;

                    axios.post('/bot/games/' + this.game.id)
                        .catch(error => {
                            console.log(error.message);
                        });

                }, Number(ms));
            },

            clearBotTimer() {
                clearTimeout(this.botTimeout);
                clearInterval(this.botInterval);
                if (this.botTimer <= 5) {
                    document.getElementById('timer').pause();
                }
                this.botTimer = this.init_bot_timer / 1000;
                this.showBotTimer = false;
                this.jokerCardCancelled = false;
            },

            // ppm = player position map?
            // TODO: change admin check logic
            canKickUser(n) {
                if (App.user.id === this.game.user_id || App.user.username.toLowerCase() === 'admin') {
                    if (this.game.state === 'start' && this.game.players[this.ppm[n]] !== undefined) {
                        return true;
                    }
                }
                return false;
            },

            cancelJok(t) {
                $(t).addClass('d-none');
                this.playState = true;
                this.card = {};
                this.jokerCardCancelled = true;
            },

            checkLastCards(lastCards) {
                for (let card of lastCards) {
                    if (card === undefined) return false;
                }
                return true;
            },

            lang(text) {
                //let locale = document.cookie.split(';')
                    //.filter((val) => {return val.trim().startsWith('lang=')})[0].slice(-2);
                let locale = App.locale;    
                if (locale === 'en' || !locale) return text;
                
                if (locale === 'ka') {
                    return this.ka[text];
                }
            }
        }
    }
</script>
