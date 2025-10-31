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
    path: '/ed',
    name: 'ed',
    component: () => import(/* webpackChunkName: "about" */ '../views/costomeredit.vue')
  },
  {
    path: '/p',
    name: 'product',
    component: () => import(/* webpackChunkName: "about" */ '../views/product.vue')
  },
  {
    path: '/pe',
    name: 'productedit',
    component: () => import(/* webpackChunkName: "about" */ '../views/productedit.vue')
  },
  {
    path: '/ap',
    name: 'addproduct',
    component: () => import(/* webpackChunkName: "about" */ '../views/addProduct.vue')
  },
  {
    path: '/em',
    name: 'empolyees',
    component: () => import(/* webpackChunkName: "about" */ '../views/empolyees.vue')
  },
  {
    path: '/add',
    name: 'Addstudent',
    component: () => import(/* webpackChunkName: "about" */ '../views/Add_student.vue')
  },
  {
    path: '/student',
    name: 'student',
    component: () => import(/* webpackChunkName: "about" */ '../views/student.vue')
  },
  {
    path: '/log',
    name: 'login',
    component: () => import(/* webpackChunkName: "about" */ '../views/logincoustumer.vue')
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
// 🧠 Navigation Guard — ตรวจสอบการเข้าสู่ระบบ
router.beforeEach((to, from, next) => {
  const isLoggedIn = localStorage.getItem("customer_login") === "true";

  // ถ้าหน้านั้นต้องล็อกอินก่อน แต่ยังไม่ได้ล็อกอิน
  if (to.meta.requiresAuth && !isLoggedIn) {
    alert("⚠ กรุณาเข้าสู่ระบบก่อนใช้งานหน้านี้");
    next("/login");
  }
  // ถ้าเข้าสู่ระบบแล้วแต่พยายามกลับไปหน้า login อีก → ส่งกลับหน้าแรก
  else if (to.path === "/login" && isLoggedIn) {
    next("/");
  } 
  // อื่น ๆ ไปต่อได้ตามปกติ
  else {
    next();
  }
});

export default router;

