<template>
    <div id="scoreboard" class="d-none d-md-block px-2">
        <table class="table table-bordered">
            <thead>
                <tr class="table-info">
                    <th scope="col">#</th>
                    <th scope="col" v-for="p in [0,1,2,3]" v-text="players[p] ? players[p].username : '#'+(p+1)"></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="n in [0,1,2,3,4,5,6,7,8]" :class="n === 8 ? 'table-info' : ''">
                    <th scope="row">{{ n === 8 ? '&Sigma;' : n + 1 }}</th>
                    <td v-for="p in [0,1,2,3]" v-html="n === 8 ? showResult(p , 1, n) : showScores(p , 1, n )"></td>
                </tr>
                <tr v-for="n in [0,1,2,3,4]" :class="n === 4 ? 'table-info' : ''">
                    <th scope="row">{{ n === 4 ? '&Sigma;' : 9 }}</th>
                    <td v-for="p in [0,1,2,3]" v-html="n === 4 ? showResult(p , 2, n) : showScores(p , 2, n )"></td>
                </tr>
                <tr v-for="n in [0,1,2,3,4,5,6,7,8]" :class="n === 8 ? 'table-info' : ''">
                    <th scope="row">{{ n === 8 ? '&Sigma;' : 8 - n }}</th>
                    <td v-for="p in [0,1,2,3]" v-html="n === 8 ? showResult(p , 3, n) : showScores(p , 3, n )"></td>
                </tr>
                <tr v-for="n in [0,1,2,3,4]" :class="n === 4 ? 'table-info' : ''">
                    <th scope="row">{{ n === 4 ? '&Sigma;' : 9 }}</th>
                    <td v-for="p in [0,1,2,3]" v-html="n === 4 ? showResult(p , 4, n) : showScores(p , 4, n )"></td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="table-info">
                    <th scope="row">$</th>
                    <td colspan="4" class="fw-bold">{{ this.penalty }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</template>

<script>
    import scoreboard from "../mixins/scoreboard";

    export default {
        mixins: [scoreboard]
    }
</script>
