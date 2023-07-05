<script setup>
import { useRoute } from 'vue-router';
import { onMounted, reactive, ref } from 'vue'
import axios from 'axios'

import Loader from "../components/shared/Loader.vue";
import Pagination from "../components/shared/Pagination.vue"
import Fields from '../components/Fields.vue';

const field_settings_open = ref(false)

const route = useRoute();
const submissions = ref([]);
const submission_ids = ref([])

const selected_submission_ids = ref([])
const all_selectd = ref(false);

const loading = ref(false)
const current_page = ref(1)
const fields = ref({})
const fields_alias = ref({})
const visible_fields = ref([])
const form_id = ref(route.params.form_id)
const total_pages = ref(1)
const s_per_page = ref(10)

const csvFileDownloadLink = ref('')

const csvDownloadButton = ref(null)


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

      fields_alias.value = {}
      visible_fields.value = []
     
      for (var k in fields.value) {
        if (fields.value.hasOwnProperty(k)) {
          fields_alias.value[ fields.value[k].name ] = fields.value[k].alias
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

    obj[submission_id]['id'] = submission_id;
    obj[submission_id][field] = value;

    return obj;
  }, {});
  // console.log(submissions)
  return submissions;
}

function handleSaveFieldsSettings() {
  field_settings_open.value = false
  fetchSubmissions(form_id.value, current_page.value, s_per_page.value);
}

async function getCSV() {
  let data = 
  JSON.stringify({ 
      'visible_fields': visible_fields.value, 
      'fields_alias': fields_alias.value, 
      'form_id': form_id.value, 
      'submission_ids': selected_submission_ids.value, 

  })

  await axios.post(
    `/wp-json/efthakharcf7db/v1/getcsv`,
    data,
    {
      headers: {
        'content-type': 'application/json',
        'X-WP-Nonce': efthakharcf7db.nonce
      }
    })
    .then((response) => {
      csvFileDownloadLink.value = response.data.csv_download_link
      // console.log(csvDownloadButton.value.href)
    })
    .catch(error => console.log(error))
    csvDownloadButton.value.click()
}

function select_all() {
  if (all_selectd.value == false) {
    selected_submission_ids.value = [];
    submission_ids.value.forEach((element) => {
      selected_submission_ids.value.push(element);
    });
    all_selectd.value = true;
  } else {
    all_selectd.value = false;
    selected_submission_ids.value = [];
  }
}

async function deleteSelected() {
  let data = 
  JSON.stringify({ 
    'submission_ids': selected_submission_ids.value, 
  })

  await axios.post(
    `/wp-json/efthakharcf7db/v1/delete-submissions`,
    data,
    {
      headers: {
        'content-type': 'application/json',
        'X-WP-Nonce': efthakharcf7db.nonce
      }
    })
    .then((response) => {
      fetchSubmissions(form_id.value, current_page.value, s_per_page.value);
      selected_submission_ids.value = []
    })
    .catch(error => console.log('error'))
    
}

onMounted(() => {
  fetchSubmissions(form_id.value, current_page.value, s_per_page.value);
});


</script>

<template>
  <div class="efcf7db-page">
    <div class="ecfdb-page-header">
      <div>
        <h2 class="wp-heading-inline"> {{ $tr.submissions }} </h2>
      </div>
      <div class="ml-auto">
        <button class="button action" @click="field_settings_open = true">
          {{ $tr.field_settings }} 
        </button>
        <a target="_blank" :href="csvFileDownloadLink" ref="csvDownloadButton" class="display-none">download</a>
        <button class="button button-primary ml-15px"  @click="getCSV"> 
          {{ $tr.export_csv }} 
        </button>
        <button v-if="selected_submission_ids.length>0"
        class="button button-primary ml-15px" 
        style="border:none; background-color: rgb(190, 11, 11) !important; color:white"  
        @click="deleteSelected">   {{ $tr.delete }}  </button>
      </div>
    </div>
    <div class="page-main-content">
      <Loader v-if="loading == true" />
      <div v-if="loading == false && visible_fields.length > 0">
        <div class="ecfdb-table-container">
          <table class="ecfdb-table">
            <thead>
              <tr>
                <th scope="col" style="width: 30px">
                  <input type="checkbox" @click="select_all" />
                </th>
                <th class="minwidth-150">{{ $tr.id }}</th>
                <th scope="col" class="minwidth-150" v-for="field in visible_fields" :key="field">
                  {{ fields_alias[field] ?? '' }}
                </th>
                <!-- <th class="maxwidth-100 minwidth-60 ml-auto">Action</th> -->
              </tr>
            </thead>
            <tbody>
              <tr v-for="submission in submissions" :key="submission.id">
                <th scope="col" style="width: 30px">
                  <input type="checkbox" v-model="selected_submission_ids"  :value="submission.id"/>
                </th>
                <td class="minwidth-150">{{ submission.id }}</td>
                <td v-for="field in visible_fields">
                  {{ submission[field] }}
                </td>
                <!-- <td>
                  <button>action</button>
                </td> -->
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
    <div class="page-modals-container" v-if="field_settings_open">
      <div class="efcf7db-page-modals">
        <Fields @SaveSettings="handleSaveFieldsSettings" :form_id=form_id :fields=fields
          v-if="field_settings_open == true" />
      </div>
    </div>
</div></template>