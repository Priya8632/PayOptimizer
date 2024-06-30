<template>
  <Grid
    v-if="conditionStatus == COUNTRY"
    style="padding-bottom: 10px;"
  >
    <GridCell :column-span="{ xs: 6, sm: 6, md: 6, lg: 12, xl: 12 }">
      <VueMultiselect
        v-model="data.countries"
        label="name"
        track-by="code"
        :options="countryLists"
        :multiple="true"
      />
    </GridCell>
  </Grid>

  <Grid
    v-if="conditionStatus == LANGUAGE"
    style="padding-bottom: 10px;"
  >
    <GridCell :column-span="{ xs: 6, sm: 6, md: 6, lg: 12, xl: 12 }">
      <VueMultiselect
        v-model="data.languages"
        label="name"
        track-by="code"
        :options="languageLists"
        :multiple="true"
      />
    </GridCell>
  </Grid>

  <Grid
    v-if="conditionStatus == CUSTOMER_TAGS"
    style="padding-bottom: 10px;"
  >
    <GridCell :column-span="{ xs: 6, sm: 6, md: 6, lg: 12, xl: 12 }">
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
              v-for="option in data.customerTags"
              :key="option"
            >
              <Tag @remove="handleRemove(option)">
                {{ option }}
              </Tag>
            </div>
          </LegacyStack>
        </template>
      </TextField>
    </GridCell>
  </Grid>

  <InlineError
    v-if="data.error"
    :message="data.error"
  />

  <div
    v-for="(method, methodIndex) in data.methods"
    :key="methodIndex"
  >
    <Grid style="padding-bottom: 10px;">
      <GridCell :column-span="{ xs: 6, sm: 2, md: 2, lg: 5, xl: 5 }">
        <Select
          v-model="method.old_method"
          placeholder="Old payment method"
          :options="paymentMethodsLists"
          :error="method.oldMethodError"
        />
      </GridCell>

      <GridCell :column-span="{ xs: 6, sm: 4, md: 4, lg: 7, xl: 7 }">
        <Grid>
          <GridCell
            :column-span="{
              xs: 5,
              sm: 5,
              md: 5,
              lg: 10,
              xl: 10,
            }"
          >
            <TextField
              v-model="method.new_method"
              auto-complete="off"
              placeholder="New payment method"
              :error="method.newMethodError"
            />
          </GridCell>

          <GridCell
            :column-span="{
              xs: 1,
              sm: 1,
              md: 1,
              lg: 2,
              xl: 2,
            }"
          >
            <Button @click="emitDeleteConfirm(index, methodIndex)">
              <Icon
                :source="DeleteIcon"
                tone="critical"
              />
            </Button>
          </GridCell>
        </Grid>
      </GridCell>
    </Grid>
  </div>
</template>

<script setup>
import VueMultiselect from 'vue-multiselect'
import { COUNTRY, LANGUAGE, CUSTOMER_TAGS } from '../constants'
import { DeleteIcon } from '../Icon'
import { ref } from 'vue'

const props = defineProps({
  data: {
    type: Object,
    required: true,
  },
  paymentMethodsLists: {
    type: Array,
    required: true,
  },
  countryLists: {
    type: Array,
    required: true,
  },
  conditionStatus: {
    type: String,
    required: true,
  },
  index: {
    type: String,
    required: true,
  },
  languageLists: {
    type: Array,
    required: true,
  },
})
const emit = defineEmits(['deleteConfirm'])
const textFieldValue = ref('')

const emitDeleteConfirm = (index, methodIndex) =>
  emit('deleteConfirm', index, methodIndex)

const setTagValue = (text) => {
  textFieldValue.value = ''
  if (text.trim() !== '') {
    if (props.data.customerTags.includes(text)) {
      textFieldValue.value = ''
      return
    }
    textFieldValue.value = ''
    props.data.customerTags.push(text)
  }
}

const handleRemove = (tag) => {
  props.data.customerTags = props.data.customerTags.filter(
    (value) => value.trim() !== tag.trim(),
  )
}
</script>

<style src="vue-multiselect/dist/vue-multiselect.css"></style>
