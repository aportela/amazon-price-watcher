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

            <div class="field has-addons">
                <div class="control is-expanded has-icons-left has-icons-right">
                    <input class="input is-small" type="email" placeholder="Type group name" v-model.trim="newGroupName" :disabled="loading" @click.prevent="$event.target.select()">
                    <span class="icon is-left">
                        <i class="fas fa-list-ul"></i>
                    </span>
                    <span class="icon is-right" v-if="! newGroupName">
                        <i class="fas fa-exclamation-triangle"></i>
                    </span>
                </div>
                <div class="control">
                    <button type="button" class="button is-info is-small" :disabled="loading || ! newGroupName" @click.prevent="onAddGroup">
                        <span class="icon is-small">
                            <i class="fas" :class="{ 'fa-plus': ! loading, 'fa-cog fa-spin': loading }"></i>
                        </span>
                        <span>Add group!</span>
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
                <tbody v-for="group, index in groups" :key="group.id">
                    <tr class="has-background-grey-lighter has-text-black" style="cursor: pointer;" @click.prevent="group.collapsed = ! group.collapsed"  @drop="onDrop($event, index)" @dragover.prevent @dragenter.prevent>
                        <th><i class="fas" :class="{ 'fa-angle-double-up': ! group.collapsed, 'fa-angle-double-down': group.collapsed }"></i> {{ group.name }} (0 products)</th>
                        <th class="has-text-right">{{ 0 }}{{ '€' }}</th>
                        <th class="has-text-right">{{ 0 }}{{ '€' }}</th>
                        <td class="has-text-right has-text-weight-bold"></td>
                        <th></th>
                        <th class="has-text-centered">
                            <button class="button is-small" @click.prevent="onDeleteGroup(group.id)" :disabled="disabled"><i class="far fa-trash-alt"></i></button>
                        </th>
                    </tr>
                    <table-row-item v-for="item in group.items" :key="item.id" :item="item" :disabled="loading"></table-row-item>
                </tbody>
                <tbody>
                    <tr class="has-background-grey-lighter has-text-black" style="cursor: pointer;" @click.prevent="hideItems = !hideItems">
                        <th colspan="6"><i class="fas" :class="{ 'fa-angle-double-up': ! hideItems, 'fa-angle-double-down': hideItems }"></i> All items</th>
                    </tr>
                    <table-row-item v-for="item in items" :key="item.id" :item="item" :disabled="loading" v-on:refresh="onRefresh(item.id)" v-on:delete="onDelete(item.id)"></table-row-item>
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
            newGroupName: null,
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
        this.onLoadGroups();
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
        onDrop(evt, groupIndex) {
            const newItemId = evt.dataTransfer.getData('itemId');
            if (! this.groups[groupIndex].items.find((item) => item.id == newItemId)) {
                const item = this.items.find((item) => item.id == newItemId);
                this.groups[groupIndex].items.push(item);
            }
        },
        onLoad: function () {
            this.loading = true;
            amazonPriceWatcherAPI.amazonPriceWatcher.search((response) => {
                this.loading = false;
                if (response.status == 200) {
                    this.items = response.data.items.map((item) => {
                        item.increment = item.previousPrice - item.currentPrice;
                        return (item);
                    });
                }
            });
        },
        onLoadGroups: function () {
            this.loading = true;
            amazonPriceWatcherAPI.amazonPriceWatcher.searchGroups((response) => {
                this.loading = false;
                if (response.status == 200) {
                    this.groups = response.data.groups.map((item) => { item.collapsed = false; return (item); });
                }
            });
        },
        onAddGroup: function () {
            this.loading = true;
            amazonPriceWatcherAPI.amazonPriceWatcher.addGroup(this.newGroupName, (response) => {
                this.loading = false;
                if (response.status == 200) {
                    this.newGroupName = null;
                    this.onLoadGroups();
                } else {
                    // TODO
                }
            });
        },
        onDeleteGroup: function (id) {
            this.loading = true;
            amazonPriceWatcherAPI.amazonPriceWatcher.deleteGroup(id, (response) => {
                this.loading = false;
                if (response.status == 200) {
                    this.onLoadGroups();
                } else {
                    // TODO
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