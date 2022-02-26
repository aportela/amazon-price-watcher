import axios from 'axios';

export default {
    amazonPriceWatcher: {
        search: function (callback) {
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
        },
        delete: function (id, callback) {
            axios.post("api/delete", { id: id }).then(
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
        },
        addGroup: function (name, callback) {
            axios.post("api/add_group", { name: name }).then(
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
        },
        deleteGroup: function (id, callback) {
            axios.post("api/delete_group", { id: id }).then(
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
        },
        addGroupItem: function (groupId, itemId, callback) {
            axios.post("api/add_group_item", { groupId: groupId, itemId: itemId }).then(
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
        },
        deleteGroupItem: function (groupId, itemId, callback) {
            axios.post("api/delete_group_item", { groupId: groupId, itemId: itemId }).then(
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
        },
        searchGroups: function (callback) {
            axios.get("api/search_groups").then(
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
        },
        refresh: function (id, callback) {
            axios.post("api/scrap", { id: id }).then(
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