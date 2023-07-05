<script setup>
import { onMounted, reactive, ref } from 'vue'
import axios from 'axios'

import Loader from "../components/shared/Loader.vue";
import Pagination from "../components/shared/Pagination.vue"
import router from '../router';

const forms = ref([]);
const loading = ref(false)
const current_page = ref(1)
const total_pages = ref(1)
const forms_per_page = ref(10)

async function fetchForms(page = current_page.value, perpage = forms_per_page.value) {
  loading.value = true
  await axios
    .get(
      `/wp-json/efthakharcf7db/v1/forms?page=${page}&perpage=${perpage}`,
      {
        headers: {
          'content-type': 'application/json',
          'X-WP-Nonce': efthakharcf7db.nonce
        }
      }
    )
    .then((response) => {
      forms.value = response.data.forms;
      current_page.value = response.data.current_page
      total_pages.value = response.data.last_page
      forms_per_page.value = perpage
    })
    .catch((errors) => {
      // console.log(errors);
    });
  loading.value = false
}

onMounted(() => {
  fetchForms();
});

</script>
<template>
  <div class="efcf7db-page py-1">
    <div class="wrap">
      <h1 class="">{{ $tr.form_list }}</h1>
      <Loader v-if="loading == true" />
      <div v-if="loading == false">
        <div class="ecfdb-table-container">
          <table class="ecfdb-table">
            <thead>
              <tr>
                <!-- <th scope="col" style="width: 30px">
                  <input type="checkbox" />
                </th> -->
                <th class="minwidth-150">{{ $tr.form_id }}</th>
                <th class="minwidth-150">{{ $tr.form_name }}</th>
                <th class="maxwidth-100 minwidth-60 ml-auto">{{ $tr.action }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="form in forms" :key="form.id">
                <!-- <th scope="col" style="width: 30px">
                  <input type="checkbox" />
                </th> -->
                <td>{{ form.cf7_id }}</td>
                <td>{{ form.name }}</td>
                <td>
                  <button class="button"
                    @click="router.push({ name: 'submissions', params: { form_id: form.cf7_id } })">
                    {{ $tr.submissions }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <Pagination v-if="loading == false && forms.length > 0"
          @pageChange="(currentPage) => fetchForms(currentPage, forms_per_page)"
          @perPageChange="(perpage) => fetchForms(1, perpage)" :total_pages="total_pages" :current_page="current_page"
          :per_page="forms_per_page" />
      </div>
    </div>
  </div>
</template>

<style>

</style>
