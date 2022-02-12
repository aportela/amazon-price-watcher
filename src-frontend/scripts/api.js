import axios from 'axios';

export default {
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