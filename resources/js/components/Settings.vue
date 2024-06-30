<template>
  <StatusBanner v-if="store.appStatus == 'disabled'" />

  <Page
    title="Settings"
    subtitle="You can set up application according to your choice."
  >
    <Loader v-if="loader" />

    <template #primaryAction>
      <Button
        variant="primary"
        @click="saveSettings"
      >
        Save
      </Button>
    </template>

    <Layout v-if="!loader">
      <LayoutAnnotatedSection
        title="App Status"
        description=""
      >
        <!-- app-status -->
        <LegacyCard sectioned>
          <LegacyStack vertical>
            <RadioButton
              v-model="appStatus"
              value="enabled"
            >
              <template #label>
                <Badge tone="success">
                  Enable
                </Badge>
              </template>

              <template #helpText>
                App will be enabled on your store, this will affect checkout for
                all customers..
              </template>
            </RadioButton>

            <RadioButton
              v-model="appStatus"
              value="disabled"
            >
              <template #label>
                <Badge>Disabled</Badge>
              </template>

              <template #helpText>
                Disable this app without deleting any customizations.
                Deactivated app will not affect checkout for your customers.
              </template>
            </RadioButton>
          </LegacyStack>
        </LegacyCard>
      </LayoutAnnotatedSection>

      <LayoutAnnotatedSection
        title="Weight Unit"
        description=""
      >
        <LegacyCard sectioned>
          <LegacyStack vertical>
            <!-- weight units -->
            <Grid>
              <GridCell :column-span="{ xs: 6, sm: 3, md: 3, lg: 3, xl: 3 }">
                <RadioButton
                  v-model="weightUnit"
                  value="KILOGRAMS"
                  label="Kilograms (kg)"
                />
              </GridCell>

              <GridCell :column-span="{ xs: 6, sm: 3, md: 3, lg: 3, xl: 3 }">
                <RadioButton
                  v-model="weightUnit"
                  value="GRAMS"
                  label="Grams (g)"
                />
              </GridCell>

              <GridCell :column-span="{ xs: 6, sm: 3, md: 3, lg: 3, xl: 3 }">
                <RadioButton
                  v-model="weightUnit"
                  value="POUNDS"
                  label="Pounds (lb)"
                />
              </GridCell>

              <GridCell :column-span="{ xs: 6, sm: 3, md: 3, lg: 3, xl: 3 }">
                <RadioButton
                  v-model="weightUnit"
                  value="OUNCES"
                  label="Ounces (oz)"
                />
              </GridCell>
            </Grid>
          </LegacyStack>
        </LegacyCard>
      </LayoutAnnotatedSection>
    </Layout>
  </Page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Loader from './common/Loader.vue'
import { toster } from '../toster'
import { useStore } from '../store/store'
import { GRAMS, KILOGRAMS, POUNDS, OUNCES } from '../constants'
import StatusBanner from './common/StatusBanner.vue'

const store = useStore()

const weightUnit = ref(GRAMS)
const loader = ref(false)
const appStatus = ref('enabled')

const saveSettings = async () => {
  loader.value = true
  const param = [
    { slug: 'weight_unit', value: weightUnit.value },
    { slug: 'app_status', value: appStatus.value },
  ]
  await axios
    .post('api/saveSettings', param)
    .then((res) => {
      if (res.data.success == true) {
        res.data.data.map(row=>{
          if(row.slug == 'app_status'){
            store.appStatus = row.value
          }
        })
        toster(res.data.message)
      } else {
        toster(res.data.message, true)
      }
      loader.value = false
    })
    .catch((error) => {
      loader.value = false
      console.log(error)
    })
}
onMounted(async () => {
  await store.getSettings()
  appStatus.value = store.appStatus
  weightUnit.value = store.weightUnit
})
</script>

<style>
.Polaris-Choice__HelpText {
  line-height: 1;
}
.Polaris-Text--subdued,
.Polaris-Choice__Label {
  font-size: 13px;
}
.Polaris-Layout {
  padding: 20px;
}
</style>
