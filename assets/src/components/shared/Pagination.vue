<script setup>
import { ref } from "vue";

const props = defineProps({
  total_pages: Number,
  current_page: Number,
  per_page: Number,
});
let item_per_page = ref(props.per_page);

const emit = defineEmits(["perPageChange"]);

function pageChange(perpage) {
  item_per_page.value = perpage
  emit("perPageChange", parseInt(perpage));
}
</script>

<template>
  <div class="pagination-perpage-container">
    <div class="tablenav-pages" v-if="total_pages > 1">
      <span class="pagination-links">
        <!-- prev -->
        <span @click="$emit('pageChange', (parseInt(current_page) - 1))" class="tablenav-pages-navspan button"
          :class="current_page == 1 ? 'display-none' : ''">
          prev
        </span>

        <!-- 1st page  -->
        <span @click="$emit('pageChange', 1)" class="tablenav-pages-navspan button"
          :class="(current_page == 1) || ((parseInt(current_page) - 1) == 1) || ((parseInt(current_page) - 2) == 1) ? 'display-none' : ''">
          {{ 1 }}
        </span>

        <span>&nbsp; </span>

        <!-- current-2  -->
        <span @click="$emit('pageChange', (parseInt(current_page) - 2))" class="tablenav-pages-navspan button"
          :class="(current_page == 1)||((parseInt(current_page)-1)==1) ? 'display-none' : ''">
          {{ (current_page - 2) }}
        </span>

        <!-- current-1  -->
        <span @click="$emit('pageChange', (parseInt(current_page) - 1))" class="tablenav-pages-navspan button"
          :class="current_page == 1 ? 'display-none' : ''">
          {{ (current_page - 1) }}
        </span>

        <!-- current -->
        <span class="tablenav-pages-navspan button button-primary">{{ current_page }}</span>

        <!-- current+1  -->
        <span @click="$emit('pageChange', (parseInt(current_page) + 1))" class="tablenav-pages-navspan button"
          :class="current_page == total_pages ? 'display-none' : ''">
          {{ (parseInt(current_page) + 1) }}
        </span>

        <!-- current+2  -->
        <span @click="$emit('pageChange', (parseInt(current_page) + 2))" class="tablenav-pages-navspan button"
          :class="(parseInt(current_page)) == total_pages || ((parseInt(current_page)+1) == total_pages) ? 'display-none' : ''">
          {{ (parseInt(current_page) + 2) }}
        </span>


        <span>&nbsp; </span>
        <!-- last page  -->
        <span @click="$emit('pageChange', (parseInt(total_pages)))" class="tablenav-pages-navspan button"
          :class="((current_page == total_pages) || ((parseInt(current_page) + 1) == total_pages) || ((parseInt(current_page) + 2) == total_pages)) ? 'display-none' : ''">
          {{ (parseInt(total_pages)) }}
        </span>

        <!-- next -->
        <span @click="$emit('pageChange', (parseInt(current_page) + 1))"
          :class="current_page == total_pages ? 'display-none' : ''" class="tablenav-pages-navspan button ">
          next
        </span>
      </span>
    </div>

    <div class="ms-auto select_per_page">
      <select v-model="item_per_page" @change="pageChange(item_per_page)" class="form-select form-select-sm"
        aria-label="form-select-sm example">
        <option value="5">5 per page</option>
        <option value="10">10 per page</option>
        <option value="20">20 per page</option>
        <option value="30">30 per page</option>
        <option value="40">40 per page</option>
        <option value="50">50 per page</option>
        <option value="100">100 per page</option>
        <option value="500">500 per page</option>
        <option value="1000">1000 per page</option>
      </select>
    </div>
  </div>
</template>

<style>
.pagination-perpage-container {
  margin: 10px 0;
}

.pagination-links .tablenav-pages-navspan {
  margin: 10px 4px !important;
}
</style>