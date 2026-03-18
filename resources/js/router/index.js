import { createRouter, createWebHistory } from 'vue-router'

import StudentIndex from '@/views/Students/Index.vue'
import StudentCreate from '@/views/Students/Create.vue'

const routes = [
  {
    path: '/admin/students',
    name: 'StudentsIndex',
    component: StudentIndex
  },
  {
    path: '/admin/students/create',
    name: 'StudentsCreate',
    component: StudentCreate
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
