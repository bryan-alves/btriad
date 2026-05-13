import { createRouter, createWebHistory } from 'vue-router'

import LoginPage from '@/views/Auth/Login.vue'
import StudentDashboard from '@/views/Student/Dashboard.vue'
import StudentProfile from '@/views/Student/Profile.vue'
import StudentRanking from '@/views/Student/Ranking.vue'
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
    path: '/student',
    redirect: '/student/dashboard'
  },
  {
    path: '/student/dashboard',
    name: 'StudentDashboard',
    component: StudentDashboard,
    meta: { requiresAuth: true, roles: ['student'] }
  },
  {
    path: '/student/profile',
    name: 'StudentProfile',
    component: StudentProfile,
    meta: { requiresAuth: true, roles: ['student'] }
  },
  {
    path: '/student/ranking',
    name: 'StudentRanking',
    component: StudentRanking,
    meta: { requiresAuth: true, roles: ['student'] }
  },
  {
    path: '/admin/students',
    name: 'StudentsIndex',
    component: StudentIndex,
    meta: { requiresAuth: true, roles: ['admin', 'instructor'] }
  },
  {
    path: '/admin/students/create',
    name: 'StudentsCreate',
    component: StudentForm,
    meta: { requiresAuth: true, roles: ['admin', 'instructor'] }
  },
  {
    path: '/admin/students/:id',
    name: 'StudentsShow',
    component: StudentForm,
    meta: { requiresAuth: true, roles: ['admin', 'instructor'] }
  },
  {
    path: '/admin/attendance-lists',
    name: 'AttendanceListsIndex',
    component: AttendanceListIndex,
    meta: { requiresAuth: true, roles: ['admin', 'instructor'] }
  },
  {
    path: '/admin/attendance-lists/create',
    name: 'AttendanceListsCreate',
    component: AttendanceListForm,
    meta: { requiresAuth: true, roles: ['admin', 'instructor'] }
  },
  {
    path: '/admin/attendance-lists/:id/edit',
    name: 'AttendanceListsEdit',
    component: AttendanceListEdit,
    meta: { requiresAuth: true, roles: ['admin', 'instructor'] }
  },
  {
    path: '/admin/student-graduations',
    name: 'StudentGraduationsIndex',
    component: StudentGraduationIndex,
    meta: { requiresAuth: true, roles: ['admin', 'instructor'] }
  },
  {
    path: '/admin/student-graduations/create',
    name: 'StudentGraduationsCreate',
    component: StudentGraduationForm,
    meta: { requiresAuth: true, roles: ['admin', 'instructor'] }
  },
  {
    path: '/admin/student-graduations/:id/edit',
    name: 'StudentGraduationsEdit',
    component: StudentGraduationEdit,
    meta: { requiresAuth: true, roles: ['admin', 'instructor'] }
  },
  {
    path: '/admin/users',
    name: 'UsersIndex',
    component: UsersIndex,
    meta: { requiresAuth: true, roles: ['admin', 'instructor'] }
  },
  {
    path: '/admin/users/create',
    name: 'UsersCreate',
    component: UsersForm,
    meta: { requiresAuth: true, roles: ['admin', 'instructor'] }
  },
  {
    path: '/admin/classes',
    name: 'ClassesIndex',
    component: ClassIndex,
    meta: { requiresAuth: true, roles: ['admin', 'instructor'] }
  },
  {
    path: '/admin/classes/create',
    name: 'ClassesCreate',
    component: ClassForm,
    meta: { requiresAuth: true, roles: ['admin', 'instructor'] }
  },
  {
    path: '/admin/classes/:id',
    name: 'ClassesEdit',
    component: ClassForm,
    meta: { requiresAuth: true, roles: ['admin', 'instructor'] }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

function getCurrentUser() {
  try {
    return JSON.parse(localStorage.getItem('user') || 'null')
  } catch {
    return null
  }
}

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  const user = getCurrentUser()
  const isAuthenticated = !!token && !!user

  const requiresAuth = to.meta.requiresAuth
  const allowedRoles = to.meta.roles || []

  if (to.path === '/login') {
    if (isAuthenticated) {
      return next(user.role === 'student' ? '/student/dashboard' : '/admin/students')
    }
    return next()
  }

  if (requiresAuth && !isAuthenticated) {
    return next('/login')
  }

  if (requiresAuth && allowedRoles.length && !allowedRoles.includes(user.role)) {
    return next(user.role === 'student' ? '/student/dashboard' : '/admin/students')
  }

  return next()
})

export default router
