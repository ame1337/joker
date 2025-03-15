export default {
    created() {
        Echo.private('game.' + this.game.id)
            .listen('UpdateGameEvent', event => {
                this.game = event.game;
                this.nextTurn = Number(event.game.turn);
            })
            .listen('SyncGameStateEvent', event => {
                if (event.turn === this.ppm[0] && this.botTimerActive === false) {
                    console.log('Desync detected');
                    this.playState = false;
                    this.clearBotTimer();

                    let password = null;
                    if (this.game.password !== null) {
                        password = { pin: this.game.password }
                    }
                    axios.post('/join/games/' + this.game.id, password)
                        .then(response => {
                            this.playState = true;
                            this.cards = response.data.cards;
                            this.game = response.data.game;
                            console.log('Synced');
                        })
                        .catch(error => {
                            console.log(error.message);
                    });
                }
            })
            .listen('PlayerKickedEvent', event => {
                if (App.user.username === event.username) {
                    Echo.leaveChannel('game.' + this.game.id);
                    Echo.leaveChannel('user.' + App.user.id);
                    $('#show_kicked').trigger('click');
                    return;
                }

                this.game.players = event.players;
                this.playerPositionsMap();

                let message = {
                    username: event.username,
                    message: this.lang('Left'),
                    notification: true
                };
                this.messages.push(message);
                this.playSound('notification');
            })
            .listen('PlayerJoinLeaveEvent', event => {
                if (event.players !== false) {
                    this.game.players = event.players;
                    this.playerPositionsMap();
                    if (event.user_id) this.game.user_id = event.user_id;
                }

                let message = {
                    username: event.username,
                    message: this.lang(event.eventName),
                    notification: true
                };
                this.messages.push(message);
                this.playSound('notification');
            })
            .listen('PlayerCallEvent', event => {
                let p = this.ppm.indexOf(event.position);
                let content = event.score.call === 0 ? '-' : event.score.call;

                if (p !== 0) {
                    $('#player' + p).attr('data-bs-content', String(content));
                    $('#player' + p).popover('show');
                    setTimeout(() => {
                        $('#player' + p).popover('dispose');
                    }, 2000);
                }

                this.game.scores[event.position].data[`q_${this.game.quarter}`].push(event.score);
                this.game.except = event.except;
                this.game.to_fill = event.to_fill;
                this.game.turn = event.turn;
                this.game.state = event.state;
                this.playState = true;
                this.showCallboard();
            })
            .listen('CardPlayEvent', event => {
                this.game.cards.push(event.card);

                //this checks if botplay was triggered and card was played by bot from server
                if (event.position === this.ppm[0]) {
                    let id = 0;
                    let cards = this.players[event.position].cards;
                    for (let idx in cards) {
                        if (Number(event.card.strength) > 14 || Number(event.card.strength) === 1) {
                            if (event.card.suit == cards[idx].suit) {
                                id = idx;
                            }
                        } else {
                            if (event.card.suit == cards[idx].suit && event.card.strength == cards[idx].strength) {
                                id = idx;
                            }
                        }
                    }

                    this.players[event.position].cards.splice(id, 1);
                } else {
                    this.players[event.position].cards.pop();
                }

                this.game.players[event.position].card = event.card;
                this.playSound('card-play');

                this.lastCardsStorage[this.ppm.indexOf(event.position)] = Object.create(event.card);
                this.lastCardsStorage[this.ppm.indexOf(event.position)].z = this.game.cards.length;

                this.hideCards(event.take);
            })
            .listen('StartGameEvent', event => {
                $('#ready').addClass('d-none');
                clearInterval(this.timerFn);

                let cards = event.cards;
                let i = 0;
                let p = 0;
                p = this.ppm.indexOf(p);

                let ace = () => {
                    $('#player' + p + 'card')
                        .css('z-index', i)
                        .removeClass()
                        .addClass(cards[i].suit + cards[i].strength);

                    this.playSound('card-play');

                    p = p === 3 ? 0 : p + 1;
                    i++;

                    if (i === cards.length) {
                        clearInterval(ace_ing);
                        setTimeout(() => {
                            for (let i = 0; i < 4; i++) {
                                $('#player' + i + 'card').removeClass();
                            }
                            this.game = event.game;
                            this.playerPositionsMap();
                            this.showCards(this.dealtCards, false);
                        }, 1500);
                    }
                }

                let ace_ing = setInterval(ace, 1000);

            })
            .listen('GetReadyEvent', event => {
                this.game.state = 'ready';
                $('#ready-check').removeClass('d-none');
                $('#ready th').eq(event.position).addClass('bg-success');
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
            })
            .listen('UpdateReadyEvent', event => {
                let color  = event.ready === '1' ? 'bg-success' : 'bg-danger';

                $('#ready th').eq(event.position).addClass(color);
            })
            .listen('UpdateTrumpEvent', event => {
                this.game.trump = event.trump;
                this.playState = true;
                this.game.state = 'call';
            })
            .listen('GameOverEvent', event => {
                this.playState = false;
                this.game = event.game;

                setTimeout(() => {
                    for (let i = 0; i < 4; i++) {
                        $(`#place-${i} img`).attr('src', this.game.players[event.scores[i].position].avatar_url);
                        $(`#place-${i} .u-name a`).attr('href', `/user/${this.game.players[event.scores[i].position].user_id}`)
                            .text(this.game.players[event.scores[i].position].username);
                    }

                    $('#game-over').removeClass('d-none');
                }, 1000);
            })
            .listen('ChatMessageEvent', event => {
                event.message.notification = false;
                this.messages.push(event.message);
                this.playSound('notification');
                this.$nextTick(() => {
                    let el = document.getElementById('messages');
                    el.scrollTo(0, el.scrollHeight);
                });
            });
        
        window.axios.interceptors.request.use((config) => {
            let socketId = Echo.socketId();
            if (socketId) {
                config.headers['X-Socket-ID'] = socketId;
            }
            return config;
        }, (error) => {
            return Promise.reject(error);
        });

        $('html').css('overflow', 'hidden');
    }
}
