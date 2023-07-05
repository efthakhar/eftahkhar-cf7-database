<script setup>
import { onMounted, reactive, ref } from 'vue'
import axios from 'axios'

const emit = defineEmits(["SaveSettings"]);

const props = defineProps({
    fields: Object, 
    form_id: Number
});

const form_fields = ref(props.fields);
const form_id = ref(props.form_id);

function save() {
    axios.put(
    
    `/wp-json/efthakharcf7db/v1/form-fields`,
    JSON.stringify({'fields': form_fields.value,'form_id':form_id.value}),
    {
        headers: {
          'content-type': 'application/json',
          'X-WP-Nonce': efthakharcf7db.nonce
        }
    })
    .then((response) => {
        console.log(response.data)
        emit("SaveSettings");
    })
    .catch(error => console.log(error))
}

onMounted(() => {
    // fetchForms();
    // console.log(fields.value)
});

</script>
<template>
    <div class="efcf7db-modal p10px">
        <table class="cf7db-list-table">
            <tr>
                <th>{{ $tr.field }}</th>
                <th>{{ $tr.alias }}</th>
                <th>{{ $tr.visible }}</th>
            </tr>
            <tr v-for="field in form_fields">
                <td>{{ field.name }}</td>
                <td><input type="text" v-model="form_fields[field['name']]['alias']"></td>
                <td><input type="checkbox" v-model="form_fields[field['name']]['visible']"></td>
            </tr>
        </table>
        <div class="my-10px">
            <button class="button button-primary" @click="save">{{ $tr.save }}</button>
            <button class="button ml-10px" @click="$emit('SaveSettings')">{{ $tr.cancle }}</button>
        </div>
    </div>
</template>

<style></style>
