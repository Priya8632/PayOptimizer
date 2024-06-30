<template>
  <Page
    :back-action="{ content: 'Settings', onAction: gotoHome }"
    title="Rename Payment Methods"
  >
    <confirm-modal
      v-if="isModalOpen == true"
      :id="isModalConfirmId"
      :open="isModalOpen"
      :title="modalTitle"
      :content="modalContent"
      :modal-value="modalValue"
      :primary-btn="isModalConfirmBtnName"
      @close="confirmModalClose"
      @delete="removeRenameField"
      @error="confirmError"
    />

    <Loader v-if="loader" />

    <template #primaryAction>
      <Button
        variant="primary"
        @click="editPaymentCustomization"
      >
        Update
      </Button>
    </template>

    <div v-if="!loader">
      <LimitBanner
        v-if="limitReachedMsg != ''"
        :limit-reached-msg="limitReachedMsg"
        :limit-reached="true"
      />

      <CustomizationStatus
        v-model:ruleTitle="rule_title"
        v-model:ruleStatus="rule_status"
        :limit-reached-msg="limitReachedMsg"
        @change-rule-status="editPaymentCustomization"
      />

      <div
        style="
          border-radius: 10px;
          border: 1px solid rgb(203 203 203);
          margin: 20px 0 20px 0;
        "
      >
        <Layout>
          <LayoutAnnotatedSection
            title="Enter payment Method"
            description=""
          >
            <LegacyCard sectioned>
              <LegacyStack vertical>
                <Grid>
                  <GridCell
                    :column-span="{ xs: 6, sm: 6, md: 6, lg: 12, xl: 12 }"
                  >
                    <Select
                      v-model="conditionStatus"
                      :options="conditionOptions"
                      @change="changeCondition"
                    />
                  </GridCell>
                </Grid>

                <Divider />

                <span style="color: red;">
                  Note : Show only activate payment methods on checkout page.
                </span>

                <!-- condition fields -->
                <div
                  v-if="store.paymentMethods.length > 0 || editField == true"
                >
                  <div style="margin-bottom: 10px;">
                    <div
                      v-for="(data, index) in renameFields"
                      :key="index"
                    >
                      <div class="payment-rule-list">
                        <div
                          v-if="conditionStatus != ALWAYS"
                          class="delete_button"
                          @click="deleteConfirmPopUp(index, '')"
                        >
                          <Icon
                            :source="DeleteIcon"
                            tone="critical"
                          />
                        </div>

                        <Renamefields
                          :index="index"
                          :data="data"
                          :country-lists="countryLists"
                          :payment-methods-lists="paymentMethodsLists"
                          :language-lists="languageLists"
                          :condition-status="conditionStatus"
                          @delete-confirm="deleteConfirmPopUp"
                        />

                        <!-- methods -->
                        <div
                          v-if="data.methods.length == 0"
                          style="margin: 10px;"
                        >
                          <InlineError
                            message="Minimum one payment method is required"
                          />
                        </div>

                        <Button
                          variant="primary"
                          @click="addMethods(index)"
                        >
                          Add More
                        </Button>
                      </div>

                      <div
                        :class="[
                          index + 1 != renameFields.length
                            ? 'container'
                            : 'deleteBtnHide',
                        ]"
                      >
                        <div class="renameline" />
                      </div>
                    </div>

                    <div
                      v-if="renameFields.length == 0"
                      style="margin: 10px;"
                    >
                      <InlineError
                        message="Minimum one customization rule is required"
                      />
                    </div>
                  </div>

                  <Divider />

                  <Button
                    v-if="conditionStatus != ALWAYS"
                    variant="primary"
                    style="margin-top: 15px;"
                    @click="addrenameField"
                  >
                    Add Condition
                  </Button>
                </div>

                <div v-else>
                  <Button
                    variant="plain"
                    @click="addPaymentMethods"
                  >
                    Add Manual payment methods
                  </Button>
                </div>
              </LegacyStack>
            </LegacyCard>
          </LayoutAnnotatedSection>
        </Layout>
      </div>
    </div>
  </Page>
</template>

<script setup>
import ConfirmModal from './common/Confirm.vue'
import CustomizationStatus from './common/CustomizationStatus.vue'
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useRoute } from 'vue-router'
import { useStore } from '../store/store'
import {
  RENAME,
  COUNTRY,
  ALWAYS,
  LANGUAGE,
  CUSTOMER_TAGS,
  addPaymentMethodsUrl,
} from '../constants'
import { toster } from '../toster'
import { DeleteIcon } from '../Icon'
import Loader from './common/Loader.vue'
import Renamefields from './Renamefields.vue'
import LimitBanner from './common/LimitBanner.vue'

const props = defineProps({
  renameData: {
    type: Array,
    required: true,
  },
})

const store = useStore()
const router = useRouter()
const route = useRoute()
const loader = ref(false)
// rule
let rule_title = ref('')
let rule_status = ref('disabled')
const customization = ref('')
const conditionStatus = ref(ALWAYS)
// country list
const countryLists = ref([])
const renameFields = ref([])
// payment-methods-list
const paymentMethodsLists = ref([])
const conditionOptions = [
  { label: 'Always', value: ALWAYS },
  { label: 'Country', value: COUNTRY },
  { label: 'Language', value: LANGUAGE },
  { label: 'Customer tags', value: CUSTOMER_TAGS },
]
const languageLists = ref([])
// confim-modal
const isModalOpen = ref(false)
const modalTitle = ref('')
const modalContent = ref('')
const isModalConfirmId = ref('')
const isModalConfirmBtnName = ref('')
const modalValue = ref('')

const editField = ref(false)
const limitReachedMsg = ref('')

//delete
const deleteConfirmPopUp = (index, methodIndex) => {
  isModalOpen.value = true
  modalTitle.value = 'Confirm Delete?'
  modalContent.value = 'Are you sure you want to delete?'
  isModalConfirmId.value = index
  isModalConfirmBtnName.value = 'Delete'
  modalValue.value = methodIndex
}
// close confirm-modal
const confirmModalClose = () => {
  isModalOpen.value = false
}

const confirmError = (text) => {
  toster(text, true)
  confirmModalClose()
}

const addPaymentMethods = () => {
  window.open(addPaymentMethodsUrl())
}

const removeRenameField = (index, methodIndex) => {
  if (methodIndex === '') {
    renameFields.value.splice(index, 1)
  } else {
    renameFields.value[index].methods.splice(methodIndex, 1)
  }
  isModalOpen.value = false
}

const addMethods = (index) => {
  renameFields.value[index].methods.push({
    old_method: '',
    new_method: '',
    oldMethodError: '',
    newMethodError: '',
  })
}
const addrenameField = () => {
  renameFields.value.push({
    countries: [],
    languages: [],
    customerTags:[],
    methods: [
      {
        old_method: '',
        new_method: '',
        oldMethodError: '',
        newMethodError: '',
      },
    ],
    error: '',
  })
}

const changeCondition = () => {
  renameFields.value = [
    {
      countries: [],
      languages: [],
      customerTags:[],
      methods: [
        {
          old_method: '',
          new_method: '',
          oldMethodError: '',
          newMethodError: '',
        },
      ],
      error: '',
    },
  ]
}

// if any backend error passed
const removeError = () => {
  renameFields.value.map((element) => {
    if (conditionStatus.value == COUNTRY) {
      if (element.countries.length > 0 || element.methods.length > 0) {
        element.error = ''
      }
    } else if (conditionStatus.value == LANGUAGE) {
      if (element.languages.length > 0 || element.methods.length > 0) {
        element.error = ''
      }
    } else if (conditionStatus.value == CUSTOMER_TAGS) {
      if (element.customerTags.length > 0 || element.methods.length > 0) {
        element.error = ''
      }
    } else if (element.methods.length > 0) {
      element.error = ''
    }
    element.methods.map((method) => {
      if (method.old_method != '') {
        method.oldMethodError = ''
      }
      if (method.new_method.trim() != '') {
        method.newMethodError = ''
      }
    })
  })
}

// save rule
const editPaymentCustomization = async () => {
  await utils.getSessionToken(app)

  if (rule_title.value.trim() == '') {
    toster('Title Field is Required', true)
    return
  }

  if (renameFields.value.length == 0) {
    return
  }

  for (const [key, value] of Object.entries(renameFields.value)) {
    if (conditionStatus.value == COUNTRY) {
      if (value.countries.length === 0) {
        value.error = 'Fields is Required'
      } else {
        value.error = ''
      }
    } else if (conditionStatus.value == LANGUAGE) {
      if (value.languages.length === 0) {
        value.error = 'Fields is Required'
      } else {
        value.error = ''
      }
    } else if (conditionStatus.value == CUSTOMER_TAGS) {
      if (value.customerTags.length === 0) {
        value.error = 'Fields is Required'
      } else {
        value.error = ''
      }
    }
    const uniqueMethods = new Set()
    for (const [key, method] of Object.entries(value.methods)) {
      if (method.old_method == '') {
        method.oldMethodError = 'Fields is Required'
      } else {
        method.oldMethodError = ''
        if (!uniqueMethods.has(method.old_method)) {
          uniqueMethods.add(method.old_method)
        } else {
          method.oldMethodError = 'Methods must be unique'
        }
      }
      if (method.new_method.trim() == '') {
        method.newMethodError = 'Fields is Required'
      } else {
        method.newMethodError = ''
      }
    }
  }

  const methods = renameFields.value.map((row) => row.methods.length === 0)
  if (methods.includes(true)) {
    return
  }

  const hasErrors = renameFields.value.some((row) => {
    if (row.error) return true
    return row.methods.some(
      (method) => method.oldMethodError || method.newMethodError,
    )
  })
  if (hasErrors) {
    // Validation false
    return
  }

  removeError()
  const param = []
  param.push({
    payment_cust_id: route.params.id,
    rule_title: rule_title.value,
    rule_status: rule_status.value,
    customization: customization.value,
    conditionStatus: conditionStatus.value,
    conditionFields: renameFields.value,
    weightUnit: store.weightUnit,
  })

  loader.value = true
  await axios
    .post('api/editPaymentCustomization', param)
    .then((res) => {
      loader.value = false
      if (res.data.success == true) {
        limitReachedMsg.value = ''
        toster(res.data.message)
      } else {
        let message = res.data.message
        if (res.data.limitReached == true) {
          limitReachedMsg.value = message
          return
        } else if (res.data.message.rule_title) {
          message = res.data.message.rule_title[0]
        } else if (res.data.message.conditionFields) {
          message = res.data.message.conditionFields[0]
        } else {
          for (const key in res.data.message) {
            if (
              res.data.message[key].old_method ||
              res.data.message[key].new_method
            ) {
              for (const [fieldkey, value] of Object.entries(
                renameFields.value,
              )) {
                for (const [methodkey, method] of Object.entries(
                  value.methods,
                )) {
                  if (method.old_method == '') {
                    method.oldMethodError = res.data.message[key].old_method[0]
                  } else {
                    method.oldMethodError = ''
                  }
                  if (method.new_method.trim() == '') {
                    method.newMethodError = res.data.message[key].new_method[0]
                  } else {
                    method.newMethodError = ''
                  }
                }
              }
            } else {
              if (typeof res.data.message === 'string') {
                message = res.data.message
              } else {
                renameFields.value[key.match(/\d+/g)].error = res.data.message[
                  key
                ][0].replace(key, '')
              }
            }
          }
        }
        toster(message, true)
      }
    })
    .catch((error) => {
      loader.value = false
      console.log(error)
    })
}

// set data
const setRenameCustomizationData = () => {
  const data = props.renameData
  rule_status.value = data.status
  rule_title.value = data.title
  customization.value = data.customization.name
  const fields = JSON.parse(data.condition_fields)
  if (fields?.fields?.length > 0) {
    editField.value = true
    conditionStatus.value = fields.conditionStatus
    fields.fields.map((element, index) => {
      element.methods.map((row) => {
        if (
          !store.paymentMethods.includes(row.old_method) &&
          store.paymentMethods.length > 0
        ) {
          paymentMethodsLists.value.push(row.old_method)
        }
        if (
          !paymentMethodsLists.value.includes(row.old_method) &&
          store.paymentMethods.length == 0
        ) {
          paymentMethodsLists.value.push(row.old_method)
        }
      })
      renameFields.value.push({
        countries: element.countries,
        languages: element.languages,
        methods: element.methods,
        customerTags: element.customerTags,
        error: '',
      })
    })
  } else {
    renameFields.value.push({
      countries: [],
      languages: [],
      customerTags: [],
      methods: [
        {
          old_method: '',
          new_method: '',
          oldMethodError: '',
          newMethodError: '',
        },
      ],
      error: '',
    })
  }
}
const gotoHome = () => {
  router.push({ name: 'home' })
}
onMounted(() => {
  setRenameCustomizationData()
  store.paymentMethods.map((row) => {
    paymentMethodsLists.value.push(row)
  })
  store.countries.map((row) => {
    countryLists.value.push({
      name: row.country,
      code: row.code.toString(),
    })
  })
  store.languages.map((row) => {
    languageLists.value.push({
      name: row.name,
      code: row.isoCode.toUpperCase(),
    })
  })
})
</script>
