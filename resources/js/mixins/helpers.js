export default {
    data() {
        return {
            windowWidth: window.innerWidth,
            ppm: [],
            timer: 10,
            nextTurn: 4,
            url: window.App.url
        }
    },

    computed: {
        showStart() {
            if (App.user.username.toLowerCase() === 'admin') return false;
            return App.user.id === this.game.user_id && this.game.state === 'start' && this.game.players.length === 4;
        },

        passwordProtected() {
            return this.game.state === 'start' && this.game.password && this.game.players.length < 4;
        },

        botTimerActive() {
            let states = ['trump', 'call', 'card'];

            return this.playState && this.turn && !this.jokerCardCancelled && states.indexOf(this.game.state) >= 0;
        }
    },

    created() {
        this.playerPositionsMap();
        this.showCards(this.initialCards, true);
    },

    mounted() {
        window.onresize = this.debounce(() => {
            this.windowWidth = window.innerWidth;
        }, 200);

        this.showCallboard();

        if (this.game.state === 'trump' && this.turn) {
            $('#suits').removeClass('d-none');
        }

        this.$watch('botTimerActive', (active) => {
            if (active) {
                this.clearBotTimer();
                this.setBotTimer();
            }
        });

        if (this.botTimerActive) {
            this.setBotTimer(7000);
        }
    },

    beforeDestroy() {
        this.clearBotTimer();
    },

    // center cards with negative margins based on viewport
    methods: {
        debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        },
        
        getMargin(n) {
            let smallMargins = [
                {even: -47, odd: -34.5},
                {even: -27.5, odd: -22.5},
                {even: -21.5, odd: -16.5},
                {even: -27.5, odd: -22.5},
            ];

            let bigMargins = [
                {even: -47, odd: -34.5},
                {even: -59.5, odd: -47},
                {even: -47, odd: -34.5},
                {even: -59.5, odd: -47},
            ];

            let count = this.players[this.ppm[n]].cards.length;

            if (count % 2 === 0) {
                let base = this.windowWidth >= 576 ? bigMargins[n].even : smallMargins[n].even;
                return base - (count - 2) / 2 * this.marginStep(n);
            } else {
                let base = this.windowWidth >= 576 ? bigMargins[n].odd : smallMargins[n].odd;
                return base - (count - 1) / 2 * this.marginStep(n);
            }
        },

        marginStep(n) {
            return n === 0 || this.windowWidth >= 576 ? 25 : 10;
        },

        playerPositionsMap() {
            let position;
            for (let i = 0; i < 4; i++) {
                if (App.user.id === this.game.players[i].user_id) {
                    position = i;
                    break;
                }
            }

            switch(position) {
                case 1:
                    this.ppm = [1, 2, 3, 0];
                    break;
                case 2:
                    this.ppm = [2, 3, 0, 1];
                    break;
                case 3:
                    this.ppm = [3, 0, 1, 2];
                    break;
                default:
                    this.ppm = [0, 1, 2, 3];
                    break;
            }
        },

        getUsername(p) {
            return this.game.players[this.ppm[p]] ? this.game.players[this.ppm[p]].username : `#${p}`;
        },

        getProfileLink(p) {
            return this.game.players[this.ppm[p]] ? '/user/' + this.game.players[this.ppm[p]].user_id : '#';
        },

        getAvatarUrl(p) {
            return this.game.players[this.ppm[p]] ? this.game.players[this.ppm[p]].avatar_url : false;
        },

        playedCard(n) {
            let player = this.game.players[this.ppm[n]];
            if (player && player.card != null) {
                if (player.card['suit'].indexOf('joker') !== -1) {
                    return player.card['suit'] + '16';
                } else {
                    return player.card['suit'] + player.card['strength'];
                }
            }
        },

        cardsZIndex(n) {
            let player = this.game.players[this.ppm[n]];
            if (player && player.card != null) {
                for (let idx in this.game.cards) {
                    if (this.game.cards[idx]['suit'] == player.card['suit'] &&
                        this.game.cards[idx]['strength'] == player.card['strength']) {
                        return idx;
                    }
                }
            }
        },

        active(n) {
            let state = ['trump', 'call', 'card'];
            if (state.indexOf(this.game.state) >= 0) {
                return this.ppm[n] === this.game.turn ? 'border-warning' : '';
            } else {
                return '';
            }
        },

        playedCardAction(n) {
            let player = this.game.players[this.ppm[n]];
            if (player && player.card != null && player.card['action']) {
                this.$nextTick(() => {
                    const popoverElement = $(`#player${n}card`);
                    const popoverId = popoverElement.attr('aria-describedby');
                    const isPopoverVisible = $(`#${popoverId}`).length > 0 && 
                        $(`#${popoverId}`).hasClass('show');
        
                    if (!isPopoverVisible) {
                        popoverElement.popover('dispose');
                        popoverElement.attr('data-bs-content', this.played_card_action_html(n));
                        popoverElement.popover('show');
                    }
                });
                return true;
            } else {
                $(`#player${n}card`).popover('dispose');
                return false;
            }
        },

        played_card_action_html(n) {
            let card = this.game.players[this.ppm[n]].card;
            let action = this.actions[card['action']];
            let actionSuit = this.actionsuits[card['actionsuit']] ?? '';
            let suitColor = this.suitColor(card['actionsuit']);

            return `${action} <span class="${suitColor}">${actionSuit}</span>`;
        },

        suitColor(suit) {
            if (suit === 'hearts' || suit === 'diamonds') {
                return 'text-danger';
            } else {
                if ($('html').attr('data-bs-theme') === 'dark') {
                    return 'text-light'
                } else {
                    return 'text-dark';
                }
            }
        },

        get_card_action_placement(n) {
            let placements = ['bottom', 'left', 'top', 'right'];
            return placements[n];
        },

        kick(n) {
            axios.post('/kick/games/' + this.game.id, {position: this.ppm[n]})
                .catch(error => {
                    console.log(error.message);
                });
        },

        goToLobby() {
            window.location = '/lobby';
        },

        showChat() {
            if (this.windowWidth < 768) {
                $('#play-table').addClass('d-none');
                $('#chat-wrapper').removeClass('d-none');
                $('.close-w').removeClass('d-none');
                $('#theme-changer').addClass('d-none');
                let p = $('.p-bg-fix');
                if (p.length > 0) {
                    p.addClass('d-none');
                }
            } else {
                $('#scoreboard').removeClass('d-md-block');
                $('#chat-wrapper').removeClass('d-none');
                $('#btn-scoreboard').removeClass('d-md-none');
                $('#btn-chat').addClass('d-none');
            }
        },

        showScoreboard() {
            if (this.windowWidth < 768) {
                $('#play-table').addClass('d-none');
                $('#scoreboard').removeClass('d-none');
                $('.close-w').removeClass('d-none');
                $('#theme-changer').addClass('d-none');
                let p = $('.p-bg-fix');
                if (p.length > 0) {
                    p.addClass('d-none');
                }
            } else {
                $('#chat-wrapper').addClass('d-none');
                $('#scoreboard').addClass('d-md-block');
                $('#btn-scoreboard').addClass('d-md-none');
                $('#btn-chat').removeClass('d-none');
            }
        },

        closeW() {
            $('#play-table').removeClass('d-none');
            $('#chat-wrapper').addClass('d-none');
            $('#scoreboard').addClass('d-none');
            $('.close-w').addClass('d-none');
            $('#theme-changer').removeClass('d-none');
            let p = $('.p-bg-fix');
            if (p.length > 0) {
                p.removeClass('d-none');
            }
        },

        copyLink() {
            let link = document.getElementById("table-link");
            link.select();
            link.setSelectionRange(0, 99999);
            document.execCommand("copy");
        },

        playSound(sound) {
            if (this.muted) return;

            let audio = document.getElementById(sound);
            audio.currentTime = 0;
            audio.muted = false;
            audio.play();
        },

        showCallboard() {
            if (this.turn && this.game.state === 'call') {
                $('#callboard').removeClass('d-none');
            }
        }
    }
}
