<template>
  <Page
    :back-action="{ content: 'Settings', onAction: gotoHome }"
    title="Sort Payment Methods"
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
      @delete="removeSortField"
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

      <Layout>
        <LayoutAnnotatedSection
          title="Choose either you want to sort always or conditionally."
          description=""
        >
          <LegacyCard sectioned>
            <LegacyStack vertical>
              <!-- condition status -->
              <Grid>
                <GridCell :column-span="{ xs: 6, sm: 3, md: 3, lg: 6, xl: 6 }">
                  <RadioButton
                    v-model="conditionStatus"
                    value="conditionally"
                    label="Sort conditionally"
                  />
                </GridCell>

                <GridCell :column-span="{ xs: 6, sm: 3, md: 3, lg: 6, xl: 6 }">
                  <RadioButton
                    v-model="conditionStatus"
                    value="always"
                    label="Sort always"
                    @change="removeError"
                  />
                </GridCell>
              </Grid>
            </LegacyStack>
          </LegacyCard>
        </LayoutAnnotatedSection>
      </Layout>

      <Divider />

      <div
        style="
          border-radius: 10px;
          border: 1px solid rgb(203 203 203);
          margin: 20px 0 20px 0;
        "
      >
        <Layout>
          <LayoutAnnotatedSection
            v-if="conditionStatus == 'conditionally'"
            title="Customization Rules"
            description="You can set one condition single time only."
          >
            <LegacyCard sectioned>
              <LegacyStack vertical>
                <!-- condition fields -->
                <div
                  v-for="(data, index) in sortFields"
                  :key="index"
                >
                  <div class="payment-rule-list">
                    <div
                      class="delete_button"
                      @click="deleteConfirmPopUp(index, 'rule')"
                    >
                      <Icon
                        :source="DeleteIcon"
                        tone="critical"
                      />
                    </div>

                    <Grid style="padding-bottom: 10px;">
                      <GridCell
                        :column-span="{ xs: 6, sm: 3, md: 3, lg: 6, xl: 6 }"
                      >
                        <Select
                          v-model="data.ruleType"
                          :options="ruleTypeOptions"
                          @change="checkRuleCondition(index)"
                        />
                      </GridCell>

                      <GridCell
                        :column-span="{ xs: 6, sm: 3, md: 3, lg: 6, xl: 6 }"
                      >
                        <Select
                          v-model="data.ruleCondition"
                          :options="ruleConditionOptions"
                        />
                      </GridCell>
                    </Grid>

                    <Grid>
                      <GridCell
                        :column-span="{ xs: 6, sm: 6, md: 6, lg: 12, xl: 12 }"
                      >
                        <Sortfields
                          :data="data"
                          :country-lists="countryLists"
                        />

                        <InlineError
                          v-if="data.error"
                          :message="data.error"
                        />
                      </GridCell>
                    </Grid>
                  </div>

                  <div
                    :class="[
                      index + 1 != sortFields.length
                        ? 'container'
                        : 'deleteBtnHide',
                    ]"
                  >
                    <div class="line" />

                    <div class="text">
                      AND
                    </div>
                  </div>
                </div>

                <div
                  v-if="sortFields.length == 0"
                  style="margin: 10px;"
                >
                  <InlineError
                    message="Minimum one customization rule is required"
                  />
                </div>

                <Divider />

                <div :class="{ deleteBtnHide: sortFields.length == 2 }">
                  <Button
                    variant="primary"
                    @click="addSortField"
                  >
                    Add Condition
                  </Button>
                </div>
              </LegacyStack>
            </LegacyCard>
          </LayoutAnnotatedSection>

          <LayoutAnnotatedSection
            title="Re-order Payment Methods"
            description="Add payment methods and re-order it."
          >
            <LegacyCard sectioned>
              <LegacyStack vertical>
                <Text
                  variant="headingSm"
                  as="h6"
                >
                  Sorting Order :
                </Text>

                <Grid>
                  <GridCell
                    :column-span="{ xs: 6, sm: 3, md: 3, lg: 6, xl: 6 }"
                  >
                    <RadioButton
                      v-model="sortingValue"
                      value="asc"
                      label="First to last"
                    />
                  </GridCell>

                  <GridCell
                    :column-span="{ xs: 6, sm: 3, md: 3, lg: 6, xl: 6 }"
                  >
                    <RadioButton
                      v-model="sortingValue"
                      value="desc"
                      label="last to first"
                    />
                  </GridCell>
                </Grid>

                <Divider />

                <span style="color:red;">Note : Show only activate payment methods on checkout page.</span>

                <div
                  v-if="store.paymentMethods.length > 0 || editField == true"
                >
                  <div
                    v-if="selectedPayments.length > 0"
                    style="
                      border-radius: 10px;
                      border: 1px solid #c9c9c9;
                      margin-bottom: 10px;
                    "
                  >
                    <Grid
                      style="
                        padding: 10px;
                        border-bottom: 1px solid rgb(201, 201, 201);
                      "
                    >
                      <GridCell
                        :column-span="{ xs: 1, sm: 1, md: 1, lg: 1, xl: 1 }"
                      />

                      <GridCell
                        :column-span="{ xs: 4, sm: 4, md: 4, lg: 10, xl: 10 }"
                      >
                        <p>Title</p>
                      </GridCell>

                      <GridCell
                        class="icon_center"
                        :column-span="{ xs: 1, sm: 1, md: 1, lg: 1, xl: 1 }"
                      />
                    </Grid>

                    <div style="padding: 15px 25px 0 25px;">
                      <draggable
                        v-model="selectedPayments"
                        item-key="id"
                        @start="drag = true"
                        @end="logAllMethods"
                      >
                        <template #item="{index,element}">
                          <Grid style="margin-bottom: 15px; cursor: pointer;">
                            <GridCell
                              :column-span="{
                                xs: 1,
                                sm: 1,
                                md: 1,
                                lg: 1,
                                xl: 1,
                              }"
                            >
                              <Icon :source="DragHandleIcon" />
                            </GridCell>

                            <GridCell
                              :column-span="{
                                xs: 4,
                                sm: 4,
                                md: 4,
                                lg: 10,
                                xl: 10,
                              }"
                            >
                              <Select
                                v-model="element.name"
                                placeholder=" "
                                :options="paymentMethodsLists"
                                :error="element.error"
                                @change="setMethods(element.name, index)"
                              />
                            </GridCell>

                            <GridCell
                              class="icon_center"
                              :column-span="{
                                xs: 1,
                                sm: 1,
                                md: 1,
                                lg: 1,
                                xl: 1,
                              }"
                            >
                              <Button
                                @click="deleteConfirmPopUp(index, 'method')"
                              >
                                <Icon :source="DeleteIcon" />
                              </Button>
                            </GridCell>
                          </Grid>
                        </template>
                      </draggable>
                    </div>
                  </div>

                  <div
                    v-else
                    style="margin: 10px;"
                  >
                    <InlineError
                      message="Minimum one payment methods is required"
                    />
                  </div>

                  <Divider />

                  <Button
                    variant="primary"
                    style="margin-top:15px;"
                    @click="addMethodsField"
                  >
                    Add Payment Methods
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
import draggable from 'vuedraggable'
import Sortfields from './Sortfields.vue'
import {
  SORT,
  COUNTRY,
  SHIPPING_TITLE,
  CONTAINS,
  NOT_CONTAINS,
  addPaymentMethodsUrl,
} from '../constants'
import { toster } from '../toster'
import VueMultiselect from 'vue-multiselect'
import { DeleteIcon, DragHandleIcon } from '../Icon'
import Loader from './common/Loader.vue'
import LimitBanner from './common/LimitBanner.vue'

const props = defineProps({
  sortData: {
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
// country list
const countryLists = ref([])
// consition-status
const conditionStatus = ref('conditionally')
const textFieldValue = ref('')
// fields
const sortFields = ref([])
const sortingValue = ref('asc')
const ruleTypeOptions = [
  { label: 'Country', value: COUNTRY, disabled: false }, //dropdown
  { label: 'Shipping Title', value: SHIPPING_TITLE, disabled: false }, //popup
]
const ruleConditionOptions = [
  { label: 'Contains', value: CONTAINS },
  { label: 'Does not contains', value: NOT_CONTAINS },
]
// payment-methods-list
const paymentMethodsLists = ref([{ title: 'Payment Methods', options: [] }])
const selectedPayments = ref([{ name: '', error: '' }])
const methodsArray = ref([])
const limitReachedMsg = ref('')
const drag = ref(false)
const editField = ref(false)

// confim-modal
const isModalOpen = ref(false)
const modalTitle = ref('')
const modalContent = ref('')
const isModalConfirmId = ref('')
const isModalConfirmBtnName = ref('')
const modalValue = ref('')
//delete
const deleteConfirmPopUp = (index, value) => {
  isModalOpen.value = true
  modalTitle.value = 'Confirm Delete?'
  modalContent.value = 'Are you sure you want to delete?'
  isModalConfirmId.value = index
  isModalConfirmBtnName.value = 'Delete'
  modalValue.value = value
}
// close confirm-modal
const confirmModalClose = () => {
  isModalOpen.value = false
}

const confirmError = (text) => {
  toster(text, true)
  confirmModalClose()
}

const logAllMethods = () => {
  const reorderedArray = []
  selectedPayments.value.forEach((payment) => {
    const foundMethod = methodsArray.value.find(
      (method) => method == payment.name,
    )
    if (foundMethod) {
      reorderedArray.push(foundMethod)
    }
  })
  methodsArray.value = reorderedArray
  drag.value = false
}

const addMethodsField = () => {
  selectedPayments.value.push({ name: '', error: '' })
}
const setMethods = (method, index) => {
  methodsArray.value[index] = method
  removeDuplicates(selectedPayments.value)
}
const removeDuplicates = (array) => {
  const uniqueMethods = new Set()
  for (const [key, value] of Object.entries(array)) {
    if (!uniqueMethods.has(value.name)) {
      uniqueMethods.add(value.name)
    } else {
      value.error = 'Methods must be unique'
      continue
    }
    value.error = ''
  }
}

// when any methods not activate
const addPaymentMethods = () => {
  window.open(addPaymentMethodsUrl())
}
const addSortField = () => {
  sortFields.value.push({
    ruleType: COUNTRY,
    ruleCondition: CONTAINS,
    customerDetails: [],
    countries: [],
    error: '',
  })
}

const removeSortField = (index, value) => {
  if (value == 'rule') {
    sortFields.value.splice(index, 1)
  } else if (value == 'method') {
    selectedPayments.value.splice(index, 1)
    methodsArray.value.splice(index, 1)
  }
  isModalOpen.value = false
}

const checkRuleCondition = (index) => {
  sortFields.value[index].ruleCondition = CONTAINS
  sortFields.value[index].customerDetails = []
  sortFields.value[index].countries = []
}

const gotoHome = () => {
  router.push({ name: 'home' })
}

// if any conditional error exist then remove
const removeError = () => {
  sortFields.value.map((row) => {
    if (row.error != '') {
      row.error = ''
    }
  })
}

// save rule
const editPaymentCustomization = async () => {
  await utils.getSessionToken(app)

  if (rule_title.value.trim() == '') {
    toster('Title Field is Required', true)
    return
  }

  if (conditionStatus.value == 'conditionally') {
    for (const [key, value] of Object.entries(sortFields.value)) {
      if (value.ruleType === SHIPPING_TITLE) {
        if (value.customerDetails.length === 0) {
          value.error = 'Field is Required'
          continue
        }
      } else if (value.ruleType === COUNTRY) {
        if (value.countries.length === 0) {
          value.error = 'Field is Required'
          continue
        }
      }
      value.error = ''
    }
  }

  if (selectedPayments.value.length == 0 || sortFields.value.length == 0) {
    return
  }

  const uniqueMethods = new Set()
  for (const [key, value] of Object.entries(selectedPayments.value)) {
    if (value.name == '') {
      value.error = 'Field is Required'
      continue
    }
    if (!uniqueMethods.has(value.name)) {
      uniqueMethods.add(value.name)
    } else {
      value.error = 'Methods must be unique'
      continue
    }
    value.error = ''
  }

  const inValidArray = sortFields.value.map((row) => row.error == '')

  if (
    inValidArray.includes(false) ||
    selectedPayments.value.map((row) => row.error == '').includes(false)
  ) {
    // validation false
    return
  }

  const param = []
  param.push({
    payment_cust_id: route.params.id,
    rule_title: rule_title.value,
    rule_status: rule_status.value,
    customization: customization.value,
    conditionStatus: conditionStatus.value,
    conditionFields: sortFields.value,
    sortingValue: sortingValue.value,
    pay_methods: methodsArray.value,
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
        } else if (res.data.message.pay_methods) {
          message = res.data.message.pay_methods[0]
        } else if (res.data.message.conditionFields) {
          message = res.data.message.conditionFields[0]
        } else {
          for (const key in res.data.message) {
            if (res.data.message[key].customerDetails) {
              message = res.data.message[key].customerDetails[0]
            } else if (res.data.message[key].countries) {
              message = res.data.message[key].countries[0]
            }
            toster(message, true)
            return
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
const setSortCustomizationData = () => {
  const data = props.sortData

  rule_status.value = data.status
  rule_title.value = data.title
  customization.value = data.customization.name
  const fields = JSON.parse(data.condition_fields)
  if (fields?.fields?.length > 0) {
    editField.value = true
    conditionStatus.value = fields.conditionStatus
    sortingValue.value = fields.sortingValue
    fields.fields.map((element, index) => {
      sortFields.value.push({
        ruleType: element.ruleType,
        ruleCondition: element.ruleCondition,
        customerDetails: element.customerDetails,
        countries: element.countries,
        error: '',
      })
    })

    selectedPayments.value = []
    fields.paymentMethods.map((element, index) => {
      selectedPayments.value.push({ name: element, error: '' })
      methodsArray.value.push(element)
    })
    if (store.paymentMethods.length == 0) {
      fields.paymentMethods.map((row) => {
        paymentMethodsLists.value[0].options.push({ label: row, value: row })
      })
    } else {
      fields.paymentMethods.map((method) => {
        const foundMethod = store.paymentMethods.find(
          (payment) => payment == method,
        )
        if (!foundMethod) {
          paymentMethodsLists.value[0].options.push({
            label: method,
            value: method,
          })
        }
      })
    }
  } else {
    sortFields.value.push({
      ruleType: COUNTRY,
      ruleCondition: CONTAINS,
      customerDetails: [],
      countries: [],
      error: '',
    })
  }
}
onMounted(() => {
  setSortCustomizationData()
  store.paymentMethods.map((row) => {
    paymentMethodsLists.value[0].options.push({ label: row, value: row })
  })
  store.countries.map((row) => {
    countryLists.value.push({
      name: row.country,
      code: row.code.toString(),
    })
  })
})
</script>
