<template>
  <StatusBanner v-if="store.appStatus == 'disabled'" />

  <Page
    :back-action="{ content: 'Settings', onAction: gotoHome }"
    title="Choose Your Customization"
  >
    <Loader v-if="loader" />

    <Modal
      sectioned
      :open="active"
      :primary-action="primaryAction"
      :secondary-actions="secondaryActions"
      :click-outside-to-close="false"
      @close="active = false"
    >
      <template #title>
        Create Payment Customization
      </template>

      <p>Shopify Payment Customization title (For internal use only)</p>

      <TextField
        v-model="title"
        type="text"
        label=""
        :error="error"
        auto-complete="on"
        required-indicator
      />
    </Modal>

    <LegacyStack
      v-if="!loader"
      vertical
    >
      <div v-if="customizations.length">
        <div
          v-for="(items, index) in customizations"
          :key="index"
          style="padding-bottom: 10px;"
        >
          <Card>
            <CardSection>
              <Text
                variant="headingMd"
                as="h6"
                style="padding-bottom: 20px;"
              >
                {{ items.name }}
              </Text>

              <p>{{ JSON.parse(items.filter).join(', ') }}</p>

              <Grid style="margin-top: 10px;">
                <GridCell
                  :column-span="{ xs: 6, sm: 6, md: 6, lg: 6, xl: 6 }"
                />

                <GridCell :column-span="{ xs: 6, sm: 6, md: 6, lg: 6, xl: 6 }">
                  <div style="float: right;">
                    <Button
                      variant="plain"
                      @click="setRuleType(items.id,items.name)"
                    >
                      Create Customization
                    </Button>
                  </div>
                </GridCell>
              </Grid>
            </CardSection>
          </Card>
        </div>
      </div>

      <div v-else>
        <p style="text-align: center;">
          Data Not found
        </p>
      </div>
    </LegacyStack>
  </Page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { toster } from '../toster'
import Loader from './common/Loader.vue'
import StatusBanner from './common/StatusBanner.vue'
import { useStore } from '../store/store'

const store = useStore()
const router = useRouter()
const active = ref(false)
const title = ref('')
const customizations = ref([])
const customizationId = ref('')
const customizationName = ref('')
const error = ref('')
const loader = ref(false)
const setRuleType = (id,name) => {
  active.value = true
  customizationId.value = id
  customizationName.value = name
  title.value = ''
  error.value = ''
}

const primaryAction = {
  content: 'Create',
  onAction: async () => {
    if (title.value == '') {
      error.value = 'This field is required.'
      return
    }

    active.value = false
    loader.value = true
    await utils.getSessionToken(app)
    await axios
      .post('api/createPaymentCustomization', {
        title: title.value,
        customization_id: customizationId.value,
        customization_name:customizationName.value
      })
      .then((res) => {
        loader.value = false
        if (res.data.success == true) {
          router.push({ name: 'home' })
        } else {
          active.value = true
          if (res.data.message.title[0]) {
            toster(res.data.message.title[0], true)
            return
          }
          toster(res.data.message, true)
        }
      })
      .catch((error) => {
        loader.value = false
        console.log(error)
      })
  },
}

const secondaryActions = [
  {
    content: 'Close',
    onAction: () => {
      active.value = false
      error.value = ''
    },
  },
]

const gotoHome = () => {
  router.push({ name: 'home' })
}

const getCustomizations = async () => {
  loader.value = true
  await utils.getSessionToken(app)
  await axios
    .get('api/getCustomizations')
    .then((res) => {
      if (res.data.success) {
        customizations.value = res.data.data
      }
      loader.value = false
    })
    .catch((error) => {
      loader.value = false
      console.log(error)
    })
}

onMounted(async () => {
  await getCustomizations()
})
</script>
