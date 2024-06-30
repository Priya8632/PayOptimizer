<template>
  <div>
    <Modal
      :open="open"
      :primary-action="{
        content: primaryBtn ?? 'Submit',
        onAction: clickConfirm,
      }"
      :secondary-actions="[{ content: 'Close', onAction: closeClick }]"
      :click-outside-to-close="false"
      @close="closeClick"
    >
      <template #title>
        {{ title ?? 'Default Title' }}
      </template>

      <p style="padding: 17px;">
        {{ content ?? 'Default Content' }}
      </p>
    </Modal>
  </div>
</template>

<script setup>
const props = defineProps({
  open: {
    type: Boolean,
    required: true,
  },
  title: {
    type: String,
    required: true,
  },
  content: {
    type: String,
    required: true,
  },
  primaryBtn: {
    type: String,
    required: true,
  },
  id: {
    type: String,
    required: true,
  },
  modalValue: {
    type: String,
    required: true,
  },
})
const emit = defineEmits(['close', 'delete', 'error'])

const closeClick = () => {
  emit('close')
}
const clickConfirm = () => {
  if (props.id != null) {
    emit('delete', props.id, props.modalValue)
  } else {
    emit('error', 'id is invalid')
  }
}
</script>
