import './bootstrap';
import { createApp } from 'vue'
import AppPollShow from './AppPollShow.vue'

const el = document.getElementById('app')
const props = JSON.parse(el.dataset.props)

createApp(AppPollShow, props).mount(el)
