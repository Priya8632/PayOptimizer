<template>
  <StatusBanner v-if="store.appStatus == 'disabled'" />

  <Loader v-if="loader" />

  <Hide
    v-if="customization == HIDE"
    :hide-data="hideFields"
  />

  <Rename
    v-if="customization == RENAME"
    :rename-data="renameFields"
  />

  <Sort
    v-if="customization == SORT"
    :sort-data="sortFields"
  />
</template>

<script setup>
import Hide from './Hide.vue'
import Rename from './Rename.vue'
import Sort from './Sort.vue'
import StatusBanner from './common/StatusBanner.vue'
import { useStore } from '../store/store'
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { HIDE, RENAME, SORT } from '../constants'
import Loader from './common/Loader.vue'

const store = useStore()
const route = useRoute()
// hide,rename,sort
const customization = ref('')
const hideFields = ref([])
const renameFields = ref([])
const sortFields = ref([])
const loader = ref(false)

// get data
const getPaymentCustomization = async () => {
  loader.value = true
  await utils.getSessionToken(app)
  const id = route.params.id
  await axios
    .get(`api/getPaymentCustomization/${id}`)
    .then((res) => {
      if (res.data.success) {
        customization.value = res.data.data?.customization.name
        if (customization.value == HIDE) {
          hideFields.value = res.data.data
        } else if (customization.value == RENAME) {
          renameFields.value = res.data.data
        } else if (customization.value == SORT) {
          sortFields.value = res.data.data
        }
      }
      loader.value = false
    })
    .catch((error) => {
      loader.value = false
      console.log(error)
    })
}
onMounted(async () => {
  loader.value = true
  await store.getSettings()
  await store.getPaymentMethods()
  await store.getCountryList()
  await store.getLanguageList()
  await getPaymentCustomization()
})
</script>

<style>
.Polaris-Choice__HelpText {
  line-height: 1;
}
.Polaris-Text--subdued,
.Polaris-Choice__Label {
  font-size: 13px;
}
.Polaris-Layout {
  padding: 20px;
}

.payment-rule-list {
  background-color: #edeeef;
  padding: 15px 10px 10px;
  border-radius: 8px;
  position: relative;
}

.payment-rule-list .delete_button {
  position: absolute;
  top: -10px;
  right: -10px;
  height: 25px;
  width: 25px;
}
.Polaris-Icon {
  margin-top: 2px;
}
.delete_button {
  cursor: pointer;
  background-color: #d7d7d7;
  border-radius: inherit;
}
.deleteBtnHide {
  display: none;
}
.container {
  position: relative;
  display: inline-block;
  display: flex;
  justify-content: center;
  align-items: center;
}

.line {
  border-left: 1px dashed black;
  height: 50px;
}
.renameline {
  border-left: 1px dashed black;
  height: 25px;
}

.text {
  position: absolute;
  top: 15px;
  left: 50%;
  transform: translateX(-50%);
  text-transform: uppercase;
  font-weight: 700;
  border: 1px solid #000;
  border-radius: 50px;
  padding: 0 15px;
  background: #fff;
}
</style>
