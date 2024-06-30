<template>
  <div v-if="data.ruleType == SHIPPING_TITLE">
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
            <Tag @remove="removeTag(option)">
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
</template>

<script setup>
import { ref } from 'vue'
import VueMultiselect from 'vue-multiselect'
import { SHIPPING_TITLE, COUNTRY, toUpperCaseOnFirstLetter } from '../constants'

const props = defineProps({
  data: {
    type: Object,
    required: true,
  },
  countryLists: {
    type: Array,
    required: true,
  },
})

const textFieldValue = ref('')
const setTagValue = (text) => {
  textFieldValue.value = ''
  if (text.trim() !== '') {
    if (props.data.customerDetails.includes(toUpperCaseOnFirstLetter(text))) {
      textFieldValue.value = ''
      return
    }
    textFieldValue.value = ''
    props.data.customerDetails.push(toUpperCaseOnFirstLetter(text))
  }
}

const removeTag = (tag) => {
  props.data.customerDetails = props.data.customerDetails.filter(
    (value) => value.trim() !== tag.trim(),
  )
}
</script>

<style src="vue-multiselect/dist/vue-multiselect.css"></style>
