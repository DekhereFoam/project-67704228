import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import Showproduct from '@/views/showproduct.vue'
import Menu from "../components/menu.vue"
import add_coustomer from '../views/add_coustomer.vue'

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView
  },
  {
    path: '/m',
    name: 'menu',
    component: Menu
  },
    {
    path: '/show',
    name: 'show',
    component: () => import(/* webpackChunkName: "about" */ '../views/showproduct.vue')
  },
  {
    path: '/add_coustomer',
    name: 'add_coustomer',
    component: () => import(/* webpackChunkName: "about" */ '../views/add_coustomer.vue')
  },

   {
    path: '/coust',
    name: 'coust',
    component: () => import(/* webpackChunkName: "about" */ '../views/Coustomer.vue')
  },
  {
    path: '/about',
    name: 'about',
    component: () => import(/* webpackChunkName: "about" */ '../views/AboutView.vue')
  }
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

export default router
