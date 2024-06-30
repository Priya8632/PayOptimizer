<template>
  <Grid style="padding-bottom: 10px;">
    <GridCell :column-span="{ xs: 6, sm: 3, md: 3, lg: 6, xl: 6 }">
      <Select
        v-model="data.ruleType"
        :options="ruleTypeOptions"
        @change="checkRuleCondition(data.ruleType, index)"
      />
    </GridCell>

    <GridCell :column-span="{ xs: 6, sm: 3, md: 3, lg: 6, xl: 6 }">
      <div
        v-if="
          customerDetailsArray.includes(data.ruleType) ||
            data.ruleType === COUNTRY ||
            data.ruleType === COLLECTION
        "
      >
        <Select
          v-model="data.ruleCondition"
          :options="option2"
        />
      </div>

      <div v-else>
        <Select
          v-model="data.ruleCondition"
          :options="option1"
        />
      </div>
    </GridCell>
  </Grid>

  <Grid>
    <GridCell :column-span="{ xs: 6, sm: 6, md: 6, lg: 12, xl: 12 }">
      <div v-if="customerDetailsArray.includes(data.ruleType)">
        <TextField
          v-model="textFieldValue"
          placeholder="Ex. something"
          auto-complete="off"
          @blur="setTagValue(textFieldValue)"
          @keydown.enter="setTagValue(textFieldValue)"
        >
          <template #verticalContent>
            <LegacyStack spacing="tight">
              <div
                v-for="option in data.customerDetails"
                :key="option"
              >
                <Tag @remove="handleRemove(option)">
                  {{ option }}
                </Tag>
              </div>
            </LegacyStack>
          </template>
        </TextField>
      </div>

      <div v-else-if="data.ruleType == COUNTRY">
        <VueMultiselect
          v-model="data.countries"
          label="name"
          track-by="code"
          :options="countryLists"
          :multiple="true"
        />
      </div>

      <div v-else-if="data.ruleType == COLLECTION">
        <TextField @click="openSelectPopup">
          <template #verticalContent>
            <LegacyStack spacing="tight">
              <div
                v-for="option in data.collectionTitles"
                :key="option"
              >
                <Tag @remove="openSelectPopup">
                  {{ option }}
                </Tag>
              </div>
            </LegacyStack>
          </template>
        </TextField>
      </div>

      <div v-else>
        <TextField
          v-model="data.cartDetails"
          type="text"
          auto-complete="off"
          required-indicator
          @keydown="checkDigit"
        />
      </div>

      <InlineError
        v-if="data.error"
        :message="data.error"
      />

      <span
        v-if="data.ruleType == TOTAL_WEIGHT"
        style="color: gray;"
      >
        {{ 'Please enter weight in' + ' ' }}
        <b>
          {{ convertWeightUnitText(store.weightUnit) }}
        </b>
      </span>
    </GridCell>
  </Grid>
</template>

<script setup>
import { ref } from 'vue'
import VueMultiselect from 'vue-multiselect'
import {
  CITY,
  COUNTRY,
  COLLECTION,
  toUpperCaseOnFirstLetter,
  convertWeightUnitText,
  TOTAL_WEIGHT,
  STATE_CODE,
  SHIPPING_TITLE,
  CURRENCY_CODE
} from '../constants'
import { useStore } from '../store/store'

const props = defineProps({
  data: {
    type: Object,
    required: true,
  },
  customerDetailsArray: {
    type: Array,
    required: true,
  },
  countryLists: {
    type: Array,
    required: true,
  },
  ruleTypeOptions: {
    type: Array,
    required: true,
  },
  option2: {
    type: Array,
    required: true,
  },
  option1: {
    type: Array,
    required: true,
  },
  index: {
    type: String,
    required: true,
  },
})
const emit = defineEmits('checkRuleCondition')
const store = useStore()
const textFieldValue = ref('')

const checkRuleCondition = (ruleType, index) =>
  emit('checkRuleCondition', ruleType, index)

const checkDigit = (event) => {
  const value = event.target.value

  if (!/\d/.test(event.key) && event.key !== '.' && event.key.length == 1) {
    event.preventDefault()
  } else if (event.key === '.') {
    // Allowing only one dot
    if (value.indexOf('.') !== -1) {
      event.preventDefault()
    }
  } else if (value.indexOf('.') !== -1) {
    // If there is a dot, limiting to two digits after the dot
    const decimalPart = value.split('.')[1]
    if (decimalPart.length >= 2 && event.key !== 'Backspace') {
      event.preventDefault()
    }
  }
}
const setTagValue = (text) => {
  textFieldValue.value = ''
  if (text.trim() !== '') {
    if (
      props.data.customerDetails.includes(text) ||
      props.data.customerDetails.includes(toUpperCaseOnFirstLetter(text)) ||
      props.data.customerDetails.includes(text.toUpperCase())
    ) {
      textFieldValue.value = ''
      return
    }
    textFieldValue.value = ''
    if (props.data.ruleType == CITY || props.data.ruleType == SHIPPING_TITLE) {
      props.data.customerDetails.push(toUpperCaseOnFirstLetter(text))
      return
    } else if (props.data.ruleType == STATE_CODE || props.data.ruleType == CURRENCY_CODE) {
      props.data.customerDetails.push(text.toUpperCase())
      return
    }
    props.data.customerDetails.push(text)
  }
}

const handleRemove = (tag) => {
  if (props.customerDetailsArray.includes(props.data.ruleType)) {
    props.data.customerDetails = props.data.customerDetails.filter(
      (value) => value.trim() !== tag.trim(),
    )
  }
}
const openSelectPopup = () => {
  let selectedData = props.data.selectedCollectionIds.map((element) => {
    return { id: element }
  })

  const productPicker = actions.ResourcePicker.create(app, {
    resourceType: actions.ResourcePicker.ResourceType.Collection,
    options: {
      initialSelectionIds: selectedData,
      selectMultiple: true,
      showHidden: false,
      showVariants: false,
    },
  })
  productPicker.subscribe(actions.ResourcePicker.Action.CANCEL, () => {
    productPicker.unsubscribe()
  })

  productPicker.subscribe(actions.ResourcePicker.Action.SELECT, (payload) => {
    props.data.selectedCollectionIds = payload.selection.map(
      (Collection, index) => {
        return Collection.id
      },
    )
    props.data.collectionTitles = payload.selection.map((Collection, index) => {
      return Collection.title
    })
    productPicker.unsubscribe()
  })
  productPicker.dispatch(actions.ResourcePicker.Action.OPEN)
}
</script>

<style src="vue-multiselect/dist/vue-multiselect.css"></style>
