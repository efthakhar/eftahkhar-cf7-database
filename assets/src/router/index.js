import { createRouter, createWebHistory } from 'vue-router'
import OverView from '../views/OverView.vue'


const router = createRouter({
  history: createWebHistory(document.location.origin+document.location.pathname+'?page=efthakharcf7db#'),
  routes: [
    {
      path: '/',
      name: 'overview',
      component: OverView
    },

    {
      path: '/forms',
      name: 'forms',
      component: () =>  import('../views/Forms.vue'),
    },
    {
      path: '/submissions/:form_id',
      name: 'submissions',
      component: () =>  import('../views/Submissions.vue'),
    }
  ]
})

export default router
