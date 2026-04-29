import { createApp } from 'vue'
import App from './App.vue'

// Find the mount point
const el = document.getElementById('umstufungmns-app')
console.log('UmstufungMNS App is mounting on element:', el)
if (el) {
    const app = createApp(App)
    app.mount(el)
    console.log('UmstufungMNS App successfully mounted!')
} else {
    console.error('UmstufungMNS App could not find element #umstufungmns-app')
}
