import axios from 'axios';

export default {
    amazonPriceWatcher: {
        search: function(callback) {
            axios.get("api/search").then(
                (response) => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                (error) => {
                    if (callback && typeof callback === "function") {
                        callback(error.response);
                    }
                }
            );
        }
    },
    amazon: {
        scrap: function (url, callback) {
            axios.post("api/scrap", { url: url }).then(
                (response) => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            ).catch(
                (error) => {
                    if (callback && typeof callback === "function") {
                        callback(error.response);
                    }
                }
            );
        }
    }
}