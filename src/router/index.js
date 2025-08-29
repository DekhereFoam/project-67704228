import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import Showproduct from '@/views/showproduct.vue'
import Menu from "../components/menu.vue";

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
