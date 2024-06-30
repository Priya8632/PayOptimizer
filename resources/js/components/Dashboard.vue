<template>
  <StatusBanner v-if="store.appStatus == 'disabled'" />

  <Page>
    <confirm-modal
      v-if="isModalOpen == true"
      :id="isModalConfirmId"
      :open="isModalOpen"
      :title="modalTitle"
      :content="modalContent"
      modal-value=""
      :primary-btn="isModalConfirmBtnName"
      @close="confirmModalClose"
      @delete="deletePaymentCustomization"
      @error="confirmError"
    />

    <Loader v-if="loader" />

    <div class="common">
      <Grid>
        <GridCell :column-span="{ xs: 6, sm: 6, md: 6, lg: 12, xl: 12 }">
          <span style="font-size: 20px;">Dashboard</span>
        </GridCell>
      </Grid>
    </div>

    <LimitBanner
      v-if="limitReachedMsg != ''"
      :limit-reached-msg="limitReachedMsg"
      :limit-reached="limitReached"
    />

    <CustomizationCount
      :active-customization-count="activeCustomizationCount"
      :hide-count="hideCount"
      :rename-count="renameCount"
      :sort-count="sortCount"
      :hide-enable-count="hideEnableCount"
      :sort-enable-count="sortEnableCount"
      :rename-enable-count="renameEnableCount"
    />

    <div class="common">
      <Grid>
        <GridCell :column-span="{ xs: 6, sm: 6, md: 4, lg: 6, xl: 6 }">
          <TextField
            v-model="searchValue"
            auto-complete="off"
            :clear-button="true"
            placeholder="Search"
            input-mode="text"
            @input="searchOnKeyUp"
            @clear-button-click=";(searchValue = ''), searchOnKeyUp()"
          >
            <template #prefix>
              <Icon :source="SearchIcon" />
            </template>
          </TextField>
        </GridCell>

        <GridCell :column-span="{ xs: 6, sm: 6, md: 2, lg: 6, xl: 6 }">
          <div style="float: right; display: flex;">
            <span class="switchText">All</span>

            <VueToggle
              v-model="store.activeCustomization"
              name="VueToggle"
              active-color="#353535"
              :toggled="store.activeCustomization"
              @toggle="toggleStatus"
            />

            <span class="switchText">Active</span>
          </div>
        </GridCell>
      </Grid>
    </div>

    <div
      v-if="!loader"
      class="common"
    >
      <IndexTable
        :headings="headings"
        :item-count="initiallySortedRows.length"
        :selectable="false"
      >
        <IndexTableRow
          v-for="({ id, title, customization, status, payment_cust_id },
                  index) in initiallySortedRows"
          :id="id"
          :key="id"
          :position="index"
        >
          <IndexTableCell>
            <span style="white-space: normal;">
              {{ title }}
            </span>
          </IndexTableCell>

          <IndexTableCell>{{ customization.name }}</IndexTableCell>

          <IndexTableCell>
            <VueToggle
              name="VueToggle"
              active-color="#353535"
              :toggled="status == 'enabled' ? true : false"
              @toggle="changeStatus(payment_cust_id, status, title)"
            />
          </IndexTableCell>

          <IndexTableCell :style="{ width: '0px' }">
            <!-- edit btn -->
            <Button
              style="margin-right: 10px;"
              @click="gotoEditView(payment_cust_id)"
            >
              <Icon :source="EditIcon" />
            </Button>
            <!-- delete btn -->
            <Button @click.stop.prevent="deleteConfirmPopUp(id)">
              <Icon :source="DeleteIcon" />
            </Button>
          </IndexTableCell>
        </IndexTableRow>
      </IndexTable>
    </div>

    <hr style="border-top: 0px solid lightgray;">

    <Grid
      :columns="{ xs: 1, sm: 1, md: 1, lg: 1, xl: 1 }"
      style="display: flex; justify-content: center;"
    >
      <span v-if="initiallySortedRows.length">
        {{ `Showing ${initiallySortedRows.length} of ${total} results` }}
      </span>
    </Grid>

    <Grid
      :columns="{ xs: 1, sm: 1, md: 1, lg: 1, xl: 1 }"
      style="display: flex; justify-content: end; padding-top: 15px;"
    >
      <Pagination
        v-if="initiallySortedRows.length"
        :has-previous="enablePreviousButton"
        :has-next="enableNextButton"
        :next-keys="['k']"
        :previous-keys="['j']"
        next-tooltip="Next"
        previous-tooltip="Previous"
        @previous="handlePrevious"
        @next="handleNext"
      >
        Results
      </Pagination>
    </Grid>

    <LegacyCard
      v-if="initiallySortedRows.length == 0"
      sectioned
    >
      <EmptyState
        heading="Manage your Payment Customization"
        image="https://cdn.shopify.com/s/files/1/0262/4071/2726/files/emptystate-files.png"
      >
        <p>Customization Not found so click on Create Payment Customization.</p>

        <div style="margin-top: 20px;">
          <Button
            variant="primary"
            @click.stop.prevent="
              $router.push({
                name: 'create',
              })
            "
          >
            Create Payment customization
          </Button>
        </div>
      </EmptyState>
    </LegacyCard>

    <Limitation />
  </Page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useStore } from '../store/store'
import { toster } from '../toster'
import { SearchIcon, EditIcon, DeleteIcon } from '../Icon'
import Limitation from './common/Limitation.vue'
import CustomizationCount from './common/CustomizationCount.vue'
import ConfirmModal from './common/Confirm.vue'
import Loader from './common/Loader.vue'
import VueToggle from 'vue-toggle-component'
import LimitBanner from './common/LimitBanner.vue'
import StatusBanner from './common/StatusBanner.vue'
import { getDigitValue } from '../constants'
import { useDebounceFn } from '@vueuse/core'

const store = useStore()
const router =  useRouter()
const initiallySortedRows = ref([])
const searchValue = ref('')
const perPage = ref(5)
const defaultPage = ref(0)
const enablePreviousButton = ref(false)
const enableNextButton = ref(false)
const total = ref(0)
const loader = ref(false)

const activeCustomizationCount = ref(0)
const hideCount = ref(0)
const renameCount = ref(0)
const sortCount = ref(0)
const hideEnableCount = ref(0)
const sortEnableCount = ref(0)
const renameEnableCount = ref(0)

const headings = [
  { title: 'Title' },
  { title: 'Rule Set Type' },
  { title: 'Status' },
  { title: 'Action' },
]

const limitReachedMsg = ref('')
const limitReached = ref(false)
// confim-modal
const isModalOpen = ref(false)
const modalTitle = ref('')
const modalContent = ref('')
const isModalConfirmId = ref('')
const isModalConfirmBtnName = ref('')
//delete
const deleteConfirmPopUp = (id) => {
  isModalOpen.value = true
  modalTitle.value = 'Confirm Delete?'
  modalContent.value = 'Are you sure you want to delete?'
  isModalConfirmId.value = id
  isModalConfirmBtnName.value = 'Delete'
}

// close confirm-modal
const confirmModalClose = () => {
  isModalOpen.value = false
}

const confirmError = (text) => {
  toster(text, true)
  confirmModalClose()
}
const gotoEditView = (payment_cust_id) => {
  router.push({
    name: 'edit',
    params: { id: getDigitValue(payment_cust_id) },
  })
}
const toggleStatus = async () => {
  limitReachedMsg.value = ''
  store.activeCustomization = !store.activeCustomization
  defaultPage.value = 0
  await getAllPaymentCustomizations()
}

const changeStatus = async (payment_cust_id, status, title) => {
  const param = []
  param.push({
    payment_cust_id: getDigitValue(payment_cust_id),
    rule_status: status == 'enabled' ? 'disabled' : 'enabled',
    rule_title: title,
    statusChangeOnDashboard: true,
  })
  loader.value = true
  await utils.getSessionToken(app)
  await axios
    .post('api/editPaymentCustomization', param)
    .then(async (res) => {
      if (res.data.success == true) {
        limitReachedMsg.value = ''
        toster(res.data.message)
      } else {
        limitReachedMsg.value = res.data.message
        limitReached.value = res.data?.limitReached
      }
      await getAllPaymentCustomizations()
      loader.value = false
    })
    .catch((error) => {
      loader.value = false
      console.log(error)
    })
}

const deletePaymentCustomization = async (id) => {
  isModalOpen.value = false
  loader.value = true
  await utils.getSessionToken(app)
  await axios
    .post(`api/deletePaymentCustomization/${id}`)
    .then(async (res) => {
      if (res.data.success == true) {
        toster(res.data.message)
      } else {
        toster(res.data.message, true)
      }
      limitReachedMsg.value = ''
      defaultPage.value = 0
      await getAllPaymentCustomizations()
      loader.value = false
    })
    .catch((error) => {
      loader.value = false
      console.log(error)
    })
}

const getAllPaymentCustomizations = async () => {
  loader.value = true

  await utils.getSessionToken(app)
  await axios
    .post('api/getAllPaymentCustomizations', {
      limit: perPage.value,
      offset: perPage.value * defaultPage.value,
      search: searchValue.value,
      status: store.activeCustomization,
    })
    .then((res) => {
      if (res.data.success) {
        initiallySortedRows.value = res.data.data
        total.value = res.data.total
        activeCustomizationCount.value = res.data.active
        hideCount.value = res.data.hide
        renameCount.value = res.data.rename
        sortCount.value = res.data.sort
        hideEnableCount.value = res.data.hide_enable
        sortEnableCount.value = res.data.sort_enable
        renameEnableCount.value = res.data.rename_enable

        var totalPage = Math.ceil(total.value / perPage.value)

        enablePreviousButton.value = false
        if (defaultPage.value > 0) {
          enablePreviousButton.value = true
        }

        enableNextButton.value = false
        if (
          (defaultPage.value == 0 && totalPage > 1) ||
          totalPage - 1 > defaultPage.value
        ) {
          enableNextButton.value = true
        }
      }
      loader.value = false
    })
    .catch((error) => {
      loader.value = false
      console.log(error)
    })
}

const handleNext = async () => {
  limitReachedMsg.value = ''
  defaultPage.value = defaultPage.value + 1
  await getAllPaymentCustomizations()
}
const handlePrevious = async () => {
  limitReachedMsg.value = ''
  defaultPage.value = defaultPage.value - 1
  await getAllPaymentCustomizations()
}
const searchOnKeyUp = useDebounceFn(async () => {
  limitReachedMsg.value = ''
  defaultPage.value = 0
  await getAllPaymentCustomizations()
}, 500)

onMounted(async () => {
  await store.getSettings()
  await getAllPaymentCustomizations()
})
</script>

<style scoped>
::v-deep(.Polaris-IndexTable__EmptySearchResultWrapper) {
  display: none !important;
}
.common {
  margin-top: 20px;
}
::v-deep(.m-toggle__content) {
  background-color: #ddd;
}
.switchText {
  justify-content: center;
  align-items: center;
  display: flex;
  margin-right: 10px;
}
::v-deep(.m-toggle) {
  font-size: 11px;
}
</style>
