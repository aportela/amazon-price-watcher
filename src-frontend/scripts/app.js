import { createApp, reactive } from 'vue';
import { default as router } from './routes.js';


/**
 * main app component
 */
const amazonPriceWatcherApp = {
    data: function () {
        return ({
        });
    },
    created: function() {
        if (this.$route.name != 'main') {
            this.$router.push({ name: 'main' });
        }
    }
};

let app = createApp(amazonPriceWatcherApp);

app.use(router).mount('#app');