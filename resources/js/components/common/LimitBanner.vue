<template>
  <Banner
    title="Oops, something went wrong!" 
    tone="critical"
  >
    <Text as="p">
      {{ limitReachedMsg }}
      <span v-if="limitReached == true">
        The
        <Button
          variant="plain" 
          @click="goToPaymentSettingsPage"
        >
          enabled limit
        </Button>
        of 5 has been reached from shopify.
      </span>
    </Text>
  </Banner>
</template>

<script setup>
import { limitReachedUrl } from '../../constants'
const props = defineProps({
  limitReachedMsg: {
    type: String,
    required: true,
  },
  limitReached: {
    type: Boolean,
    required: true,
  },
})

const goToPaymentSettingsPage = () => {
  window.open(limitReachedUrl())
}
</script>

<style>
.Polaris-Banner {
  margin-top: 25px;
}
.Polaris-Banner--withinPage {
  padding: 0px;
}
</style>
