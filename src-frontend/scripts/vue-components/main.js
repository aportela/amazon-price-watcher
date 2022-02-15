import { default as amazonPriceWatcherAPI } from '../api.js';

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
            <table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth" v-if="items && items.length > 0">
                <thead>
                    <tr>
                        <th style="width: 50%">Article</th>
                        <th class="has-text-right">Price</th>
                        <th class="has-text-right">Previous price</th>
                        <th class="has-text-right">Increment</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in items">
                        <td style="width: 50%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" :title="item.name">{{ item.name.substring(0, 80) }}...</td>
                        <td class="has-text-right">{{ item.price.toFixed(2) }}€</td>
                        <td class="has-text-right">{{ (item.price - 100).toFixed(2) }}€</td>
                        <td class="has-text-right has-text-weight-bold has-text-danger" v-if="item.price > 1000"><i class="fa-fw fas fa-sort-amount-up"></i> +2.34€</td>
                        <td class="has-text-right has-text-weight-bold has-text-success" v-else><i class="fas fa-sort-amount-down"></i> -4.56€</td>
                    </tr>
                </tbody>
            </table>
            <div class="card" v-if="productData">
                <div class="card-content">
                    <div class="content" v-if="productData.productName">
                        <h4 class="has-text-danger" v-if="productData.affiliate"><i class="fas fa-exclamation-triangle"></i> This post may contain affiliate links ({{ productData.affiliate }}), which means that the original link creator may receive a commission if you make a purchase using these links.</h4>
                        <h1 class="title is-4">{{ productData.productName }}</h1>
                        <div class="columns is-vcentered">
                            <div class="column is-half">
                                <p class="title is-5" v-if="productData.productPrice && productData.productCurrency">Price: {{ productData.productPrice.toFixed(2) }}{{ productData.productCurrency }}</p>
                                <figure class="image">
                                    <a :href="'https://es.camelcamelcamel.com/product/' + productData.asin" rel="noreferrer" target="_blank">
                                        <img :src="productData.chartURL" alt="Camel Camel Camel Chart">
                                    </a>
                                </figure>
                            </div>
                            <div class="column is-half">
                                <p class="title is-5" v-if="productData.productStock">Stock: {{ productData.productStock }}</p>
                                <figure class="image">
                                    <img :src="productData.imageURL" alt="Product Image" style="width: 320px;">
                                </figure>
                            </div>
                        </div>
                    </div>
                    <div class="content" v-else>
                        <p class="title is-4 has-text-danger has-text-centered" v-else-if="notFound"><i class="fas fa-exclamation-triangle"></i> Product not scraped</p>
                    </div>
                </div>
            </div>
            <p class="title is-4 has-text-danger has-text-centered" v-else-if="notFound"><i class="fas fa-exclamation-triangle"></i> Product not found</p>
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
            items: [
                {
                    url: 'https://www.amazon.es/Apple-MacBook-16-polegadas-Processador-8-Core/dp/B08T1TK63N/',
                    name: 'Apple MacBook Pro (de 16 polegadas, Touch Bar, Processador Intel Core i9 8-Core a 2,3 GHz, 16 GB RAM, 1 TB) - Prateado (2019)',
                    price: 2851.06,
                    currency: '€'
                },
                {
                    url: 'https://www.amazon.es/GIGABYTE-PORTATIL-YD-93ES648SP-i9-11980HK-Samsung/dp/B09F72HD9Y/',
                    name: 'GIGABYTE PORTATIL Aero 15 OLED YD-93ES648SP,i9-11980HK,32GB,SSD 512G+SSD1TB,15.6" Samsung UHD OLED,VGARTX 3070Q/8GB,NO ODD,W10PRO',
                    price: 3945.75,
                    currency: '€'
                },
                {
                    url: 'https://www.amazon.es/Intel-S1700-12900K-16x3-20-GEN12/dp/B09GYLB4J6/',
                    name: 'Apple MacBook Pro (de 16 polegadas, Touch Bar, Processador Intel Core i9 8-Core a 2,3 GHz, 16 GB RAM, 1 TB) - Prateado (2019)',
                    price: 602.45,
                    currency: '€'
                }
            ]
        });
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
    methods: {
        scrap: function () {
            this.loading = true;
            this.productData = null;
            this.notFound = false;
            amazonPriceWatcherAPI.amazon.scrap(this.url, (response) => {
                this.loading = false;
                if (response.status == 200) {
                    this.productData = response.data.product;
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
        }
    }
}