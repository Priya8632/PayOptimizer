<template>
  <Page
    :back-action="{ content: 'Settings', onAction: gotoHome }"
    title="Hide Payment Methods"
  >
    <confirm-modal
      v-if="isModalOpen == true"
      :id="isModalConfirmId"
      :open="isModalOpen"
      :title="modalTitle"
      :content="modalContent"
      modal-value=""
      :primary-btn="isModalConfirmBtnName"
      @close="confirmModalClose"
      @delete="removeHideField"
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
            description="Enter Payment Method to hide/show."
          >
            <LegacyCard sectioned>
              <LegacyStack vertical>
                <!-- payment-method-list -->
                <Grid>
                  <GridCell
                    :column-span="{ xs: 6, sm: 6, md: 6, lg: 12, xl: 12 }"
                  >
                    <Text
                      variant="headingSm"
                      as="h6"
                    >
                      Payment methods that you have set up on the store's
                      settings.
                    </Text>

                    <VueMultiselect
                      v-if="paymentMethodsLists.length > 0"
                      v-model="selectedPayments"
                      :options="paymentMethodsLists"
                      :multiple="true"
                    />

                    <Button
                      v-else
                      variant="plain"
                      @click="addPaymentMethods"
                    >
                      Add Manual payment methods
                    </Button>

                    <p style="color: red; padding-top: 10px;">
                      Note : Show only activate payment methods on checkout
                      page.
                    </p>
                  </GridCell>
                </Grid>

                <!-- condition status -->
                <Grid>
                  <GridCell
                    :column-span="{ xs: 6, sm: 3, md: 3, lg: 6, xl: 6 }"
                  >
                    <RadioButton
                      v-model="conditionStatus"
                      value="hide"
                      label="Hide"
                      help-text="Hide method when the below conditions match"
                    />
                  </GridCell>

                  <GridCell
                    :column-span="{ xs: 6, sm: 3, md: 3, lg: 6, xl: 6 }"
                  >
                    <RadioButton
                      v-model="conditionStatus"
                      value="show"
                      label="Show"
                      help-text="Show method when the below conditions match"
                    />
                  </GridCell>
                </Grid>
              </LegacyStack>
            </LegacyCard>
          </LayoutAnnotatedSection>

          <LayoutAnnotatedSection
            title="Customization Rules"
            description="You can set one condition single time only."
          >
            <LegacyCard sectioned>
              <LegacyStack vertical>
                <!-- select operator -->
                <Grid>
                  <GridCell
                    :column-span="{ xs: 3, sm: 3, md: 3, lg: 6, xl: 6 }"
                  >
                    <RadioButton
                      v-model="operator"
                      value="and"
                      label="All Below"
                    />
                  </GridCell>

                  <GridCell
                    :column-span="{ xs: 3, sm: 3, md: 3, lg: 6, xl: 6 }"
                  >
                    <RadioButton
                      v-model="operator"
                      value="or"
                      label="Any Below"
                    />
                  </GridCell>
                </Grid>

                <Divider />

                <!-- condition fields -->
                <div
                  v-for="(data, index) in hideFields"
                  :key="index"
                >
                  <div class="payment-rule-list">
                    <div
                      class="delete_button"
                      @click="deleteConfirmPopUp(index)"
                    >
                      <Icon
                        :source="DeleteIcon"
                        tone="critical"
                      />
                    </div>

                    <Hidefields
                      :data="data"
                      :index="index"
                      :rule-type-options="ruleTypeOptions"
                      :option1="option1"
                      :option2="option2"
                      :customer-details-array="customerDetailsArray"
                      :country-lists="countryLists"
                      @check-rule-condition="checkRuleCondition"
                    />
                  </div>

                  <div
                    :class="[
                      index + 1 != hideFields.length
                        ? 'container'
                        : 'deleteBtnHide',
                    ]"
                  >
                    <div class="line" />

                    <div class="text">
                      {{ operator }}
                    </div>
                  </div>
                </div>

                <div v-if="hideFields.length == 0">
                  <InlineError
                    message="Minimum one customization rule is required"
                  />
                </div>

                <Divider />

                <Button
                  variant="primary"
                  @click="addHideField"
                >
                  Add Condition
                </Button>
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
import Hidefields from './Hidefields.vue'
import CustomizationStatus from './common/CustomizationStatus.vue'
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useRoute } from 'vue-router'
import { useStore } from '../store/store'
import {
  HIDE,
  TOTAL_AMOUNT,
  SUBTOTAL_AMOUNT,
  TOTAL_QUANTITY,
  TOTAL_MONEY_SPEND,
  ZIP_CODE,
  CITY,
  COUNTRY,
  COLLECTION,
  TOTAL_WEIGHT,
  SKU,
  GREATER,
  LESS,
  CONTAINS,
  NOT_CONTAINS,
  GRAMS,
  KILOGRAMS,
  POUNDS,
  OUNCES,
  STATE_CODE,
  CUSTOMER_TAGS,
  SHIPPING_TITLE,
  TOTAL_DISCOUNT,
  DISCOUNT_RATE,
  SHIPPING_COST,
  CURRENCY_CODE,
  addPaymentMethodsUrl,
} from '../constants'
import { toster } from '../toster'
import VueMultiselect from 'vue-multiselect'
import { DeleteIcon } from '../Icon'
import Loader from './common/Loader.vue'
import LimitBanner from './common/LimitBanner.vue'

const props = defineProps({
  hideData: {
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
// payment-methods-list
const paymentMethodsLists = ref([])
const selectedPayments = ref([])
// country list
const countryLists = ref([])
// consition-status
const conditionStatus = ref('hide')
// logical-operator
const operator = ref('and')
// fields
const hideFields = ref([])
const customerDetailsArray = ref([
  ZIP_CODE,
  CITY,
  SKU,
  STATE_CODE,
  CUSTOMER_TAGS,
  SHIPPING_TITLE,
  CURRENCY_CODE
])
const ruleTypeOptions = [
  {
    title: 'Cart Details',
    options: [
      { label: 'Total Amount', value: TOTAL_AMOUNT },
      { label: 'SubTotal Amount', value: SUBTOTAL_AMOUNT },
      { label: 'Total Weight', value: TOTAL_WEIGHT },
      { label: 'Total Quantity', value: TOTAL_QUANTITY },
      { label: 'Total Discount', value: TOTAL_DISCOUNT },
      { label: 'Discount Rate', value: DISCOUNT_RATE },
      { label: 'Shipping Cost', value: SHIPPING_COST },
    ],
  },
  {
    title: 'Cart Items',
    options: [
      { label: 'Sku', value: SKU }, // array
      { label: 'Choose Collection', value: COLLECTION }, //popup
    ],
  },
  {
    title: 'Address',
    options: [
      { label: 'Country', value: COUNTRY }, //dropdown
      { label: 'Currency Code', value: CURRENCY_CODE }, //dropdown
      { label: 'Province Code / State Code', value: STATE_CODE }, // array
      { label: 'Zip Code / Postal Code', value: ZIP_CODE }, // array
      { label: 'City', value: CITY }, // array
    ],
  },
  {
    title: 'Customer',
    options: [
      { label: 'Total Spend', value: TOTAL_MONEY_SPEND },
      { label: 'Customer tags', value: CUSTOMER_TAGS },
    ],
  },
  {
    title: 'Delivery / Shipping',
    options: [{ label: 'Title', value: SHIPPING_TITLE }],
  },
]
const option1 = [
  { label: 'Greater than or equals', value: GREATER },
  { label: 'Less than or equals', value: LESS },
]
const option2 = [
  { label: 'Contains', value: CONTAINS },
  { label: 'Does not contains', value: NOT_CONTAINS },
]
const limitReachedMsg = ref('')
// confim-modal
const isModalOpen = ref(false)
const modalTitle = ref('')
const modalContent = ref('')
const isModalConfirmId = ref('')
const isModalConfirmBtnName = ref('')
//delete
const deleteConfirmPopUp = (index) => {
  isModalOpen.value = true
  modalTitle.value = 'Confirm Delete?'
  modalContent.value = 'Are you sure you want to delete?'
  isModalConfirmId.value = index
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

const addPaymentMethods = () => {
  window.open(addPaymentMethodsUrl())
}

const checkRuleCondition = (rule, index) => {
  if (
    customerDetailsArray.value.includes(rule) ||
    rule == COUNTRY ||
    rule == COLLECTION
  ) {
    hideFields.value[index].ruleCondition = CONTAINS
  } else {
    hideFields.value[index].ruleCondition = GREATER
  }
  hideFields.value[index].cartDetails = ''
  hideFields.value[index].customerDetails = []
  hideFields.value[index].countries = []
  hideFields.value[index].collectionTitles = []
  hideFields.value[index].selectedCollectionIds = []
}

const removeHideField = (index) => {
  hideFields.value.splice(index, 1)
  isModalOpen.value = false
}

const addHideField = () => {
  hideFields.value.push({
    ruleType: TOTAL_AMOUNT,
    ruleCondition: GREATER,
    cartDetails: '',
    customerDetails: [],
    countries: [],
    selectedCollectionIds: [],
    collectionTitles: [],
    error: '',
  })
}

// save rule
const editPaymentCustomization = async () => {
  await utils.getSessionToken(app)

  if (rule_title.value.trim() == '') {
    toster('Title Field is Required', true)
    return
  }
  if (selectedPayments.value.length == 0) {
    toster('Need to add one Payment-Methods.', true)
    return
  }

  if (hideFields.value.length == 0) {
    return
  }

  for (const [key, value] of Object.entries(hideFields.value)) {
    if (customerDetailsArray.value.includes(value.ruleType)) {
      if (value.customerDetails.length === 0) {
        value.error = 'Field is Required'
        continue
      }
    } else if (value.ruleType === COUNTRY) {
      if (value.countries.length === 0) {
        value.error = 'Field is Required'
        continue
      }
    } else if (value.ruleType == COLLECTION) {
      if (value.selectedCollectionIds.length === 0) {
        value.error = 'Field is Required'
        continue
      }
    } else {
      if (value.cartDetails === '') {
        value.error = 'Field is Required'
        continue
      }
      if (value.cartDetails.search(/^\d+(\.\d{2})?$/)) {
        value.error = 'Invalid Format'
        continue
      }
    }

    value.error = ''
  }

  const inValidArray = hideFields.value.map((row) => row.error == '')
  if (inValidArray.includes(false)) {
    // validation false
    return
  }

  const param = []
  param.push({
    payment_cust_id: route.params.id,
    rule_title: rule_title.value,
    rule_status: rule_status.value,
    customization: customization.value,
    pay_methods: selectedPayments.value,
    conditionStatus: conditionStatus.value,
    operator: operator.value,
    conditionFields: hideFields.value,
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
            if (res.data.message[key].cartDetails) {
              message = res.data.message[key].cartDetails[0]
            } else if (res.data.message[key].customerDetails) {
              message = res.data.message[key].customerDetails[0]
            } else if (res.data.message[key].countries) {
              message = res.data.message[key].countries[0]
            } else if (res.data.message[key].selectedCollectionIds) {
              message = res.data.message[key].selectedCollectionIds[0]
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
const setHideCustomizationData = () => {
  const data = props.hideData

  rule_status.value = data.status
  rule_title.value = data.title
  customization.value = data.customization.name
  const fields = JSON.parse(data.condition_fields)

  if (fields?.fields?.length > 0) {
    operator.value = fields.operator
    conditionStatus.value = fields.conditionStatus
    selectedPayments.value = fields.paymentMethods
    if (store.paymentMethods.length == 0) {
      fields.paymentMethods.map((row) => {
        paymentMethodsLists.value.push(row)
      })
    }

    fields.fields.map((element, index) => {
      hideFields.value.push({
        ruleType: element.ruleType,
        ruleCondition: element.ruleCondition,
        cartDetails: element.cartDetails,
        customerDetails: element.customerDetails,
        countries: element.countries,
        selectedCollectionIds: element.selectedCollectionIds,
        collectionTitles: element.collectionTitles,
        error: '',
      })
    })
  } else {
    hideFields.value.push({
      ruleType: TOTAL_AMOUNT,
      ruleCondition: GREATER,
      cartDetails: '',
      customerDetails: [],
      countries: [],
      selectedCollectionIds: [],
      collectionTitles: [],
      error: '',
    })
  }
}

const gotoHome = () => {
  router.push({ name: 'home' })
}
onMounted(() => {
  setHideCustomizationData()
  store.paymentMethods.map((row) => {
    paymentMethodsLists.value.push(row)
  })
  store.countries.map((row) => {
    countryLists.value.push({
      name: row.country,
      code: row.code.toString(),
    })
  })
})
</script>
