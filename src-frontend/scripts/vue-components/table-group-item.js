import { default as amazonPriceWatcherAPI } from '../api.js';
import { default as tableItem } from './table-product-item.js';

const template = function () {
    return `
        <tbody @drop="onDropProduct($event)" @dragover.prevent @dragenter.prevent>
            <tr class="has-text-black is-unselectable is-vcentered" :class="{ 'has-background-primary-light': incrementPrice > 0, 'has-background-danger-light': incrementPrice < 0, 'has-background-grey-lighter': incrementPrice == 0 }" style="cursor: pointer;" @click.prevent="expanded = ! expanded">
                <th>
                    <button class="button is-small is-fullwidth" :disabled="disabled || loading"><i class="fas" :class="{ 'fa-angle-double-up': expanded, 'fa-angle-double-down': ! expanded }"></i></button>
                </th>
                <th>{{ group.name }} ({{ group.items.length }} products) <input type="text" placeholder="search product by name..." v-if="! group.id && expanded" v-model.trim="searchArticleByName" @click.prevent.stop></th>
                <th class="has-text-right">{{ currentPrice.toFixed(2) }}{{ '€' }}</th>
                <th class="has-text-right">{{ previousPrice.toFixed(2) }}{{ '€' }}</th>
                <td class="has-text-right has-text-weight-bold">
                    <i class="fa-fw fas is-pulled-left mt-1" v-if="incrementPrice != 0" :class="{ 'fa-sort-amount-down': incrementPrice > 0, 'fa-sort-amount-up': incrementPrice < 0 }"></i>
                    <span v-if="incrementPrice > 0">-</span>
                    <span v-else-if="incrementPrice < 0">+</span>
                    {{ Math.abs(incrementPrice).toFixed(2) }}{{ '€' }}
                </td>
                <th></th>
                <th class="has-text-centered">
                    <button class="button is-small is-fullwidth" v-if="group.id" @click.prevent.stop="onDeleteGroup(group.id)" :disabled="disabled || loading"><i class="far fa-trash-alt"></i></button>
                </th>
            </tr>
            <table-row-item v-for="item in visibleItems" :key="item.id" :group="group" :item="item" :disabled="disabled || loading" v-on:refresh-product="onRefreshProduct(item.id)" v-on:delete-product="onDeleteGroupProduct(group.id, item.id)"></table-row-item>
        </tbody>
    `;
};

export default {
    name: 'table-group-item',
    template: template(),
    data: function () {
        return ({
            loading: false,
            expanded: false,
            currentPrice: 0,
            previousPrice: 0,
            searchArticleByName: null
        });
    },
    props: [
        'group',
        'item',
        'disabled'
    ],
    emits: [
        'addProduct', 'deleteGroup'
    ],
    watch: {
        items: function(newValue, oldValue) {
            this.currentPrice = 0;
            this.previousPrice = 0;
            newValue.forEach((item) => {
                this.currentPrice += item.currentPrice;
                this.previousPrice += item.previousPrice;
            });
        },
        searchArticleByName: function(newValue, oldValue) {

        }
    },
    created: function() {
        this.expanded = this.group && this.group.id == null;
    },
    computed: {
        items: function() {
            return(this.group.items);
        },
        visibleItems: function() {
            if (! this.searchArticleByName) {
                return(this.expanded ? this.group.items: []);
            } else {
                if (this.expanded) {
                    return(this.group.items.filter((item) => item.name.toLocaleLowerCase().indexOf(this.searchArticleByName.toLocaleLowerCase()) >= 0));
                } else {
                    return([]);
                }
            }
        },
        incrementPrice: function() {
            return(this.previousPrice - this.currentPrice);
        }
    },
    components: {
        'table-row-item': tableItem
    },
    methods: {
        onDropProduct(evt) {
            if (! this.expanded) {
                this.expanded = true;
            }
            this.$emit("addProduct", { productId: evt.dataTransfer.getData('productId')});
        },
        onRefreshProduct: function(productId) {

        },
        onDeleteGroup: function (groupId) {
            this.$emit("deleteGroup", { groupId: groupId });
        },
        onDeleteGroupProduct: function (groupId, productId) {
            this.$emit("deleteGroupProduct", { groupId: groupId, productId: productId });
        }
    }
}