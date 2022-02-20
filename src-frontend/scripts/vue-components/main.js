import { default as amazonPriceWatcherAPI } from '../api.js';
import { default as tableItem } from './table-item.js';

const template = function () {
    return `
        <section class="section">
            <div class="field has-addons">
                <div class="control is-expanded has-icons-left has-icons-right">
                    <input class="input is-medium" type="email" placeholder="Type amazon URL" v-model.trim="url" :disabled="loading" @click.prevent="$event.target.select()">
                    <span class="icon is-left">
                        <i class="fab fa-amazon"></i>
                    </span>
                    <span class="icon is-right" v-if="isValidURL">
                        <i class="fas fa-check"></i>
                    </span>
                    <span class="icon is-right" v-if="! isValidURL">
                        <i class="fas fa-exclamation-triangle"></i>
                    </span>
                </div>
                <div class="control">
                <button type="button" class="button is-info is-medium" :disabled="loading || ! isValidURL" @click.prevent="scrap">
                    <span class="icon is-small">
                        <i class="fas" :class="{ 'fa-search': ! loading, 'fa-cog fa-spin': loading }"></i>
                    </span>
                    <span>Scrap!</span>
                </button>
                </div>
            </div>
            <table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth">
                <thead>
                    <tr>
                        <th style="width: 50%" @click.prevent="onSort('name')">Article</th>
                        <th class="has-text-right" @click.prevent="onSort('currentPrice')">Price</th>
                        <th class="has-text-right" @click.prevent="onSort('previousPrice')">Previous price</th>
                        <th class="has-text-right" @click.prevent="onSort('increment')">Increment</th>
                        <th @click.prevent="onSort('lastUpdate')">Updated</th>
                        <th>Operations</th>
                    </tr>
                </thead>
                <tbody v-for="group in groups">
                    <tr class="has-background-grey-lighter has-text-black" style="cursor: pointer;" @click.prevent="hideItems = !hideItems">
                        <th><i class="fas" :class="{ 'fa-angle-double-up': ! hideItems, 'fa-angle-double-down': hideItems }"></i> {{ group.name }}</th>
                        <th class="has-text-right">{{ group.price }}{{ group.currency }}</th>
                        <th class="has-text-right">{{ group.previousPrice }}{{ group.currency }}</th>
                        <td class="has-text-right has-text-weight-bold has-text-danger"><i class="fa-fw fas fa-sort-amount-up is-pulled-left"></i> <span class="is-pulled-right">{{ (group.previousPrice - group.price).toFixed(2) }}{{ group.currency }}</span></td>
                        <th>1 minute ago</th>
                        <th></th>
                    </tr>
                    <table-row-item v-for="item in items" :item="item" :disabled="loading" v-on:refresh="onRefresh(item.id)" v-on:delete="onDelete(item.id)"></table-row-item>
                </tbody>
                <tbody>
                    <tr class="has-background-grey-lighter has-text-black" style="cursor: pointer;" @click.prevent="hideItems = !hideItems">
                        <th colspan="6">Orphaned items</th>
                    </tr>
                    <table-row-item v-for="item in items" :item="item" :disabled="loading" v-on:refresh="onRefresh(item.id)" v-on:delete="onDelete(item.id)"></table-row-item>
                </tbody>
            </table>
        </section>
    `;
};

export default {
    name: 'amazon-price-watcher-main',
    template: template(),
    data: function () {
        return ({
            loading: false,
            url: null,
            isValidURL: false,
            productData: null,
            notFound: false,
            hideItems: false,
            groups: [],
            items: [],
            sortByField: 'lastUpdate',
            sortOrder: 'DESC'
        });
    },
    created: function () {
        this.onLoad();
    },
    computed: {
        /*
        validURL: function () {
            if (this.url) {
                return (true);
            } else {
                return (false);
            }
        },
        invalidURL: function () {
            if (!this.url) {
                return (true);
            } else {
                return (false);
            }
        },
        */
    },
    watch: {
        url: function (newValue, oldValue) {
            if (newValue) {
                let parsedURL = null;
                let isValid = false;
                // https://stackoverflow.com/a/43467144
                try {
                    parsedURL = new URL(newValue);
                    isValid = true;
                } catch (e) {
                }
                this.isValidURL = isValid && (parsedURL.protocol === "http:" || parsedURL.protocol === "https:");
            } else {
                this.isValidURL = false;
            }
        }
    },
    components: {
        'table-row-item': tableItem
    },
    methods: {
        onLoad: function () {
            this.loading = true;
            amazonPriceWatcherAPI.amazonPriceWatcher.search((response) => {
                this.loading = false;
                if (response.status == 200) {
                    this.groups = response.data.groups;
                    this.items = response.data.items.map((item) => {
                        item.increment = item.previousPrice - item.currentPrice;
                        return (item);
                    });
                }
            });
        },
        scrap: function () {
            this.loading = true;
            this.productData = null;
            this.notFound = false;
            amazonPriceWatcherAPI.amazon.scrap(this.url, (response) => {
                this.loading = false;
                if (response.status == 200) {
                    this.productData = response.data.product;
                    this.url = null;
                    this.onLoad();
                } else {
                    switch (response.status) {
                        case 400:
                            this.isValidURL = false;
                        default:
                        case 404:
                            this.notFound = true;
                            break;
                            break;
                    }
                }
            });
        },
        onDelete: function (id) {
            this.loading = true;
            amazonPriceWatcherAPI.amazonPriceWatcher.delete(id, (response) => {
                this.loading = false;
                if (response.status == 200) {
                    this.onLoad();
                } else {
                }
            });
        },
        onShowItemDetails: function (item) {
            this.productData = item;
        },
        onRefresh: function (id) {
            this.loading = true;
            this.productData = null;
            this.notFound = false;
            amazonPriceWatcherAPI.amazon.refresh(id, (response) => {
                this.loading = false;
                if (response.status == 200) {
                } else {
                }
            });
        },
        onSort: function (field) {
            this.items.sort(this.sortBy(field));
        },
        // https://medium.com/@asadise/sorting-a-json-array-according-one-property-in-javascript-18b1d22cd9e9
        sortBy: function (property) {
            this.sortOrder = this.sortOrder == 'DESC' ? 'ASC' : 'DESC';
            if (this.sortOrder == 'ASC') {
                return function (a, b) {
                    if (a[property] > b[property])
                        return 1;
                    else if (a[property] < b[property])
                        return -1;
                    return 0;
                }
            } else {
                return function (a, b) {
                    if (a[property] < b[property])
                        return 1;
                    else if (a[property] > b[property])
                        return -1;
                    return 0;
                }
            }
        }
    }
}