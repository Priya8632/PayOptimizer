<template>
  <StatusBanner v-if="store.appStatus == 'disabled'" />

  <Page>
    <Loader v-if="loader" />

    <div v-if="!loader">
      <div class="common">
        <Grid>
          <GridCell :column-span="{ xs: 6, sm: 6, md: 6, lg: 12, xl: 12 }">
            <span style="font-size: 20px;">Get Support</span>
          </GridCell>
        </Grid>
      </div>

      <Layout>
        <LayoutSection>
          <Card
            v-if="!showMail && !loader"
            rounded-above="sm"
          >
            <CardSection>
              <div style="padding: 10px;">
                <div>
                  <TextField
                    v-model="formData.name"
                    auto-complete="off"
                    placeholder="Enter Your Name"
                    label="Name"
                  />

                  <InlineError
                    v-if="error.name"
                    :message="error.name[0]"
                  />
                </div>

                <div>
                  <TextField
                    v-model="formData.email"
                    auto-complete="off"
                    placeholder="Enter Your Email"
                    label="Contact email address"
                  />

                  <InlineError
                    v-if="error.email"
                    :message="error.email[0]"
                  />
                </div>

                <div>
                  <TextField
                    v-model="formData.message"
                    auto-complete="off"
                    :multiline="7"
                    placeholder="Write in detail about your query"
                    label="Message"
                  />

                  <InlineError
                    v-if="error.message"
                    :message="error.message[0]"
                  />
                </div>

                <Button
                  variant="primary"
                  style="margin-top: 10px;"
                  @click="saveSupport()"
                >
                  Submit
                </Button>
              </div>
            </CardSection>
          </Card>

          <Card v-if="showMail">
            <div class="support-thanks-div-1">
              <img
                src="image/reshot-icon-email-UERZ83AW2P.svg"
                height="50px"
                width="50px;"
              >
            </div>

            <div class="support-thanks-div-2">
              <p>
                <span class="">
                  Thank you for reaching out to us. We'll check your query
                  <br>
                  and help you with the same at the earliest.
                </span>
              </p>
            </div>

            <div class="support-thanks-div-3">
              <p>
                <span class="">
                  Please note that we'll be contacting you at the provided
                  <br>
                  email address:
                  <b>{{ formData.email }}</b>
                </span>
              </p>
            </div>
          </Card>
        </LayoutSection>

        <LayoutSection variant="oneThird">
          <Card rounded-above="sm">
            <CardSection>
              <div style="margin-bottom: 16px;">
                <Text
                  variant="headingSm"
                  as="h6"
                >
                  RESPONSE TIME
                </Text>
              </div>

              <div style="margin-bottom: 8px;">
                <p>
                  We are a growing team, but we try to reply within 24 hours.
                  Thank you for your patience while we look into your query and
                  get back to you.
                </p>
              </div>
            </CardSection>
          </Card>
        </LayoutSection>
      </Layout>
    </div>
  </Page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import StatusBanner from './common/StatusBanner.vue'
import { useStore } from '../store/store'
import Loader from './common/Loader.vue'

const store = useStore()
const loader = ref(false)
const error = ref({})
const showMail = ref(false)
const formData = ref({ name: '', email: '', message: '' })

const saveSupport = async () => {
  let hasFrontendErrors = false
  for (const field in formData.value) {
    if (formData.value[field] === '') {
      error.value[field] = [
        `${field.charAt(0).toUpperCase() + field.slice(1)} is required`,
      ]
      hasFrontendErrors = true
    } else {
      error.value[field] = []
    }
  }

  if (!hasFrontendErrors) {
    loader.value = true
    await axios
      .post(`api/saveSupport`, {
        name: formData.value.name,
        email: formData.value.email,
        message: formData.value.message,
      })
      .then((response) => {
        if (response.data.success == true) {
          error.value = {}
          showMail.value = true
        } else {
          error.value = response.data.message
        }
        loader.value = false
      })
      .catch((error) => {
        loader.value = false
        console.log(error)
      })
  }
}
onMounted(async () => {
  await store.getSettings()
})
</script>

<style>
.common {
  margin: 20px 0 20px 0;
}
.Polaris-Label__Text {
  font-size: 13px;
  font-weight: 500;
}
.support-thanks-div-1 {
  text-align: center;
  padding-top: 30px;
}
.support-thanks-div-2 {
  text-align: center;
  padding-top: 32px;
}
.support-thanks-div-3 {
  text-align: center;
  margin-top: 24px;
  padding-bottom: 30px;
}
</style>
