require('./bootstrap')

import { createApp } from 'vue'
import PolarisVue from '@ownego/polaris-vue'
import '@ownego/polaris-vue/dist/style.css'
import router from './router'
import { createPinia } from 'pinia'

import App from './components/App.vue'
const pinia = createPinia()

const app = createApp(App)
app.use(PolarisVue)
app.use(router)
app.use(pinia)
app.mount('#app')

