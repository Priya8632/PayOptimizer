<template>
  <Page>
    <Banner
      title="The app is in disabled mode."
      tone="warning"
    >
      <Text as="p">
        Please
        <Button
          variant="plain"
          @click="goToSettingPage"
        >
          click here
        </Button>
        to enable this app from setting.
      </Text>
    </Banner>
  </Page>
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

const goToSettingPage = () => {
  router.push({ name: 'Settings' })
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