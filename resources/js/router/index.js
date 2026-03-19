import { createRouter, createWebHistory } from 'vue-router'

import StudentIndex from '@/views/Students/Index.vue'
import StudentForm from '@/views/Students/Form.vue'

const routes = [
  {
    path: '/admin/students',
    name: 'StudentsIndex',
    component: StudentIndex
  },
  {
    path: '/admin/students/create',
    name: 'StudentsCreate',
    component: StudentForm
  },
  {
    path: '/admin/students/:id',
    name: 'StudentsShow',
    component: StudentForm
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
