import { createWebHashHistory, createRouter, START_LOCATION } from "vue-router";
import { default as mainPage } from './vue-components/main.js';

/**
 * vue-router route definitions
 */
const routes = [
    { path: '/main', name: 'main', component: mainPage }
];

/**
* main vue-router component inicialization
*/
const router = createRouter({
    history: createWebHashHistory(),
    routes
});

export default router;