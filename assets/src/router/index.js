import {
  createRouter,
  createWebHistory
} from 'vue-router'
import AdminView from '../views/Admin.vue'


const router = createRouter({
  history: createWebHistory(efthakharcf7db.plugin_main_page_url),
  routes: [{
      path: '/',
      name: 'admin',
      component: AdminView,
      children: [
        {
          path: '',
          name: 'overview',
          // component: () => import('../views/OverView.vue'),
          redirect: { name: 'forms' }
        },
        {
          path: '/forms',
          name: 'forms',
          component: () => import('../views/Forms.vue'),
        },
        {
          path: '/submissions/:form_id',
          name: 'submissions',
          component: () => import('../views/Submissions.vue'),
        }
      ]
    },

    
  ]
})

export default router