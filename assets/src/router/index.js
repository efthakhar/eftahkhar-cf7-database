import {
  createRouter,
  createWebHistory
} from 'vue-router'
import AdminView from '../views/Admin.vue'


const router = createRouter({
  history: createWebHistory(document.location.origin + document.location.pathname + '?page=efthakharcf7db#'),
  routes: [{
      path: '/',
      name: 'admin',
      component: AdminView,
      children: [
        {
          path: '',
          name: 'overview',
          component: () => import('../views/OverView.vue'),
        },
        {
          path: '/forms',
          name: 'forms',
          component: () => import('../views/Forms.vue'),
        }
      ]
    },

    // {
    //   path: '/forms',
    //   name: 'forms',
    //   component: () => import('../views/Forms.vue'),
    // },
    {
      path: '/submissions/:form_id',
      name: 'submissions',
      component: () => import('../views/Submissions.vue'),
    }
  ]
})

export default router