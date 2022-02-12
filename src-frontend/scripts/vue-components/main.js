import { default as amazonPriceWatcherAPI } from '../api.js';

const template = function () {
    return `
        <section class="section">
            <div class="field has-addons">
                <div class="control is-expanded has-icons-left has-icons-right">
                    <input class="input is-medium" type="email" placeholder="Type amazon URL" v-model.trim="url" :disabled="loading" readonly>
                    <span class="icon is-left">
                        <i class="fab fa-amazon"></i>
                    </span>
                    <span class="icon is-right" v-if="validURL">
                        <i class="fas fa-check"></i>
                    </span>
                    <span class="icon is-right" v-if="invalidURL">
                        <i class="fas fa-exclamation-triangle"></i>
                    </span>
                </div>
                <div class="control">
                <button type="button" class="button is-info is-medium" :disabled="loading || invalidURL" @click.prevent="scrap">
                    <span class="icon is-small">
                        <i class="fas" :class="{ 'fa-search': ! loading, 'fa-cog fa-spin': loading }"></i>
                    </span>
                    <span>Scrap!</span>
                </button>
                </div>
            </div>
            <div class="card" v-if="productData">
                <div class="card-content">
                    <div class="content">
                        <h1>{{ productData.productName }}</h1>
                        <div class="columns">
                            <div class="column is-half">
                                <h2>Price: {{ productData.productPrice }}</h2>
                                <figure class="image is-4by3">
                                <img :src="productData.chartURL" alt="Camel Camel Camel Chart">
                                </figure>
                            </div>
                            <div class="column is-half">
                                <h2>Stock: {{ productData.productStock }}</h2>
                                <figure class="image is-4by3">
                                    <img :src="productData.imageURL" alt="Product Image">
                                </figure>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    `;
};

export default {
    name: 'amazon-price-watcher-main',
    template: template(),
    data: function () {
        return ({
            loading: false,
            url: 'https://www.amazon.es/gp/product/B08MV83J94/',
            productData: null
        });
    },
    computed: {
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
        }
    },
    methods: {
        scrap: function () {
            this.loading = true;
            this.productData = null;
            amazonPriceWatcherAPI.amazon.scrap(this.url, (response) => {
                this.loading = false;
                if (response.status == 200) {
                    this.productData = response.data.product;
                    console.log(this.productData);
                }
            });
        }
    }
}