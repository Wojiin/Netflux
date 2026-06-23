import { createApp } from "vue";
import App from "./App.vue";
import { router } from "./router";
import { pinia } from "./stores/pinia";
import "./index.css";

// Point d'entrée du frontend : on crée l'application Vue, puis on branche les
// plugins globaux avant de monter l'interface dans #app.
const app = createApp(App);

app.use(pinia);

app.use(router).mount("#app");
