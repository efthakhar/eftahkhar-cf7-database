<script setup>
import { useRoute } from 'vue-router';
import { onMounted, reactive, ref } from 'vue'
import axios from 'axios'

import Loader from "../components/shared/Loader.vue";
import Pagination from "../components/shared/Pagination.vue"

const route = useRoute();
const submissions = ref([]);
const submission_ids = ref([])
const loading = ref(false)
const current_page = ref(1)
const fields = ref({})
const fields_alias = ref({})
const visible_fields = ref([])
const form_id = ref(route.params.form_id)
const total_pages = ref(1)
const s_per_page = ref(10)

async function fetchSubmissions(form_id, page = current_page.value, perpage = s_per_page.value) {

  loading.value = true
  if (form_id == null) {
    loading.value = false
    return
  }
  await axios
    .get(
      `/wp-json/efthakharcf7db/v1/submissions?form_id=${form_id}&page=${page}&perpage=${perpage}`,
      {
        headers: {
          'content-type': 'application/json',
          'X-WP-Nonce': efthakharcf7db.nonce
        }
      }
    )
    .then((response) => {

      submissions.value = generate_submission_rows(response.data.entries);
      current_page.value = response.data.current_page
      total_pages.value = response.data.last_page
      s_per_page.value = perpage
      fields.value = response.data.fields
      submission_ids.value = response.data.submission_ids

      fields_alias.value = []
      visible_fields.value = []
      for (var k in fields.value) {
        if (fields.value.hasOwnProperty(k)) {
          fields_alias.value[fields.value[k].name] = fields.value[k].alias
          fields.value[k].visible == true ? visible_fields.value.push(fields.value[k].name) : ''
        }
      }

    })
    .catch((errors) => {
      //console.log(errors);
    });
  loading.value = false
}

function generate_submission_rows(entries_array) {
  let submissions = entries_array.reduce((obj, item) => {
    const { id, submission_id, field, value } = item;

    if (!obj.hasOwnProperty(submission_id)) {
      obj[submission_id] = {};
    }

    obj[submission_id]['id'] = id;
    obj[submission_id][field] = value;

    return obj;
  }, {});
  return submissions;
}

onMounted(() => {
  fetchSubmissions(form_id.value, current_page.value, s_per_page.value);
});


</script>

<template>
  <div class="efcf7db-page">
    <div class="ecfdb-page-header">
        <div><h2 class="wp-heading-inline"> Form Submissions </h2></div>
        <div class="ml-auto"> 
          <button class="button action">Fields Settings</button>
          <button class="button action ml-15px">Conditions</button>
        </div>
    </div>
    <div class="">
      <Loader v-if="loading == true" />
      <div v-if="loading == false">
        <div class="ecfdb-table-container">
          <table class="ecfdb-table">
            <thead>
              <tr>
                <th scope="col" style="width: 30px">
                  <input type="checkbox" />
                </th>
                <th scope="col" class="minwidth-150" v-for="field in visible_fields" :key="field">
                  {{ fields_alias[field] ?? '' }}
                </th>
                <th class="maxwidth-100 minwidth-60 ml-auto">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="submission in submissions" :key="submission.id">
                <th scope="col" style="width: 30px">
                  <input type="checkbox" />
                </th>
                <td v-for="field in visible_fields">
                  {{ submission[field] }}
                </td>
                <td>
                  <button>action</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- padination and per page -->
        <Pagination v-if="loading == false && submission_ids.length > 0"
          @pageChange="(currentPage) => fetchSubmissions(form_id, currentPage, s_per_page)"
          @perPageChange="(perpage) => fetchSubmissions(form_id, 1, perpage)" :total_pages="total_pages"
          :current_page="current_page" :per_page="s_per_page" />
      </div>
    </div>
  </div>
</template>