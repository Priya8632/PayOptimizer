import { defineStore } from 'pinia'
import { useLocalStorage } from '@vueuse/core'

export const useStore = defineStore('check-out', {
  state: () => ({
    paymentMethods: [],
    countries: [],
    languages:[],
    activeCustomization: useLocalStorage('active_customization', false),
    appStatus: 'enabled',
    weightUnit: 'GRAMS',
  }),
  getters: {},
  actions: {
    async getPaymentMethods() {
      await utils.getSessionToken(app)
      await axios.get(`api/payment-methods`).then((res) => {
        if (res.data.success) {
          this.paymentMethods = res.data.methods
        }
      })
    },

    async getCountryList() {
      await utils.getSessionToken(app)
      await axios.get(`api/countries`).then((res) => {
        if (res.data.success) {
          this.countries = res.data.countries
        }
      })
    },

    async getSettings() {
      await utils.getSessionToken(app)
      await axios.get(`api/getSettings`).then((res) => {
        if (res.data.success) {
          res.data.data.map((row) => {
            if (row.slug == 'app_status') {
              this.appStatus = row.value
            } else {
              this.weightUnit = row.value
            }
          })
        }
      })
    },

    async getLanguageList() {
      await utils.getSessionToken(app)
      await axios.get(`api/languages`).then((res) => {
        if (res.data.success) {
          this.languages = res.data.languages
        }
      })
    },
  },
})
