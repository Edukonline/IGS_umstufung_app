import { createApp } from 'vue'
import App from './App.vue'
import { generateUrl } from '@nextcloud/router'

const el = document.getElementById('kursumstufung-app')
if (el) {
    console.log('KursUmstufung App is mounting on element:', el)
    const app = createApp(App)
    app.mount(el)
    console.log('KursUmstufung App successfully mounted!')
} else {
    console.error('KursUmstufung App could not find element #kursumstufung-app')
}
