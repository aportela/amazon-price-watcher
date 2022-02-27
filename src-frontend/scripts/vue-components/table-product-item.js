
const template = function () {
    return `
        <tr v-if="item" class="cursor-pointer is-vcentered" :class="{'has-background-success-light': (item.previousPrice - item.currentPrice) > 0, 'has-background-danger-light': (item.previousPrice - item.currentPrice) < 0}" draggable="true" @dragstart="startDrag($event, item)">
            <td>
                <button class="button is-small is-fullwidth" @click.prevent="this.expanded = ! this.expanded"><i class="fas fa-fw" :class="{ 'fa-angle-double-up': this.expanded, 'fa-angle-double-down': ! this.expanded }"></i></button>
            </td>
            <td style="white-space2: nowrap; overflow: hidden; text-overflow: ellipsis;" :title="item.name">
                {{ item.name }}
            </td>
            <td class="has-text-right">{{ item.currentPrice.toFixed(2) }}€</td>
            <td class="has-text-right">{{ (item.previousPrice).toFixed(2) }}€</td>
            <td class="has-text-right has-text-weight-bold" v-if="(item.previousPrice - item.currentPrice) > 0"><i class="fa-fw fas fa-sort-amount-down is-pulled-left mt-2"></i> <span class="is-pulled-right">-{{ (Math.abs(item.previousPrice - item.currentPrice)).toFixed(2)}}{{ item.currency }}</span></td>
            <td class="has-text-right has-text-weight-bold" v-else-if="(item.previousPrice - item.currentPrice) < 0"><i class="fas fa-sort-amount-up is-pulled-left mt-2"></i> <span class="is-pulled-right">+{{(Math.abs(item.previousPrice - item.currentPrice)).toFixed(2) }}{{ item.currency }}</span></td>
            <td class="has-text-right has-text-weight-bold" v-else><span class="is-pulled-right">{{(0).toFixed(2) }}{{ item.currency }}</span></td>
            <td>{{ item.lastUpdate }}</td>
            <td>
                <div class="field has-addons">
                    <p class="control">
                        <button class="button is-small is-fullwidth" @click.prevent="onRefresh(item.id)" :disabled="disabled"><i class="fas fa-sync"></i></button>
                    </p>
                    <p class="control">
                        <button class="button is-small is-fullwidth" @click.prevent="onDelete(item.id)" :disabled="disabled"><i class="far fa-trash-alt"></i></button>
                    </p>
                </div>
            </td>
        </tr>
        <tr v-if="expanded">
            <td colspan="5">
                <div class="card" v-if="item">
                    <div class="card-content">
                        <div class="content" v-if="item.name">
                            <h4 class="has-text-danger" v-if="item.affiliate"><i class="fas fa-exclamation-triangle"></i> This post may contain affiliate links ({{ item.affiliate }}), which means that the original link creator may receive a commission if you make a purchase using these links.</h4>
                            <h1 class="title is-4">{{ item.name }}</h1>
                            <div class="columns is-vcentered">
                                <div class="column is-half">
                                    <p class="title is-5" v-if="item.currentPrice && item.currency">Price: {{ item.currentPrice.toFixed(2) }}{{ item.currency }}</p>
                                    <figure class="image">
                                        <a :href="'https://es.camelcamelcamel.com/product/' + item.asin" rel="noreferrer" target="_blank">
                                            <img :src="item.chartURL" alt="Camel Camel Camel Chart"  style="width: 480px; height: 320px;">
                                        </a>
                                    </figure>
                                </div>
                                <div class="column is-half">
                                    <p class="title is-5" v-if="item.stock">Stock: {{ item.stock }}</p>
                                    <figure class="image">
                                        <img :src="item.imageURL" alt="Product Image">
                                    </figure>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    `;
};

export default {
    name: 'table-row-item',
    template: template(),
    data: function () {
        return ({
            expanded: false
        });
    },
    props: [
        'group',
        'item',
        'disabled'
    ],
    emits: [
        'refreshProduct', 'deleteProduct'
    ],
    computed: {
        isVisible: function () {
            return (true);
            if (!this.group) {
                return (true);
            } else {
                return (this.item.groupIds.includes(this.group.id));
            }
        }
    },
    methods: {
        startDrag(evt, item) {
            evt.dataTransfer.dropEffect = 'move';
            evt.dataTransfer.effectAllowed = 'move';
            evt.dataTransfer.setData('productId', item.id);
        },
        onRefresh: function (productId) {
            this.$emit("refreshProduct", { productId: productId });
        },
        onDelete: function (productId) {
            this.$emit("deleteProduct", { productId: productId });
        }
    }
}