import { createRouter, createWebHistory } from 'vue-router'
import axios from 'axios'

import LoginPage from '@/views/Auth/Login.vue'
import StudentIndex from '@/views/Students/Index.vue'
import StudentForm from '@/views/Students/Form.vue'
import AttendanceListIndex from '@/views/AttendanceLists/Index.vue'
import AttendanceListForm from '@/views/AttendanceLists/Form.vue'
import AttendanceListEdit from '@/views/AttendanceLists/Edit.vue'
import StudentGraduationIndex from '@/views/StudentGraduations/Index.vue'
import StudentGraduationForm from '@/views/StudentGraduations/Form.vue'
import StudentGraduationEdit from '@/views/StudentGraduations/Edit.vue'
import ClassIndex from '@/views/Classes/Index.vue'
import ClassForm from '@/views/Classes/Form.vue'
import UsersIndex from '@/views/Users/Index.vue'
import UsersForm from '@/views/Users/Form.vue'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: LoginPage
  },
  {
    path: '/admin/students',
    name: 'StudentsIndex',
    component: StudentIndex,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin/students/create',
    name: 'StudentsCreate',
    component: StudentForm,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin/students/:id',
    name: 'StudentsShow',
    component: StudentForm,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin/attendance-lists',
    name: 'AttendanceListsIndex',
    component: AttendanceListIndex,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin/attendance-lists/create',
    name: 'AttendanceListsCreate',
    component: AttendanceListForm,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin/attendance-lists/:id/edit',
    name: 'AttendanceListsEdit',
    component: AttendanceListEdit,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin/student-graduations',
    name: 'StudentGraduationsIndex',
    component: StudentGraduationIndex,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin/student-graduations/create',
    name: 'StudentGraduationsCreate',
    component: StudentGraduationForm,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin/student-graduations/:id/edit',
    name: 'StudentGraduationsEdit',
    component: StudentGraduationEdit,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin/users',
    name: 'UsersIndex',
    component: UsersIndex,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin/users/create',
    name: 'UsersCreate',
    component: UsersForm,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin/classes',
    name: 'ClassesIndex',
    component: ClassIndex,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin/classes/create',
    name: 'ClassesCreate',
    component: ClassForm,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin/classes/:id',
    name: 'ClassesEdit',
    component: ClassForm,
    meta: { requiresAuth: true }
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const isAuthenticated = !!localStorage.getItem('token')

  if (to.meta.requiresAuth && !isAuthenticated) {
    next('/login')
  } else if (to.path === '/login' && isAuthenticated) {
    next('/admin/students')
  } else {
    next()
  }
})

export default router
