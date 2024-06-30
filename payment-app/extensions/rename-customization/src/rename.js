// @ts-nocheck
import { RENAME, ALWAYS } from '../../../../resources/js/constants'
// Use JSDoc annotations for type safety
/**
 * @typedef {import("../generated/api").RunInput} RunInput
 * @typedef {import("../generated/api").FunctionRunResult} FunctionRunResult
 */

/**
 * @type {FunctionRunResult}
 */
const NO_CHANGES = {
  operations: [],
}

// The configured entrypoint for the 'purchase.payment-customization.run' extension target
/**
 * @param {RunInput} input
 * @returns {FunctionRunResult}
 */
export function run(input) {
  const configuration = JSON.parse(
    input?.paymentCustomization?.metafield?.value ?? '{}',
  )
  let allOperations = []

  if (!configuration?.fields?.length) {
    return NO_CHANGES
  }

  if (configuration.app_status == 'enabled') {
    /*  ------------------------------- RENAME START ------------------------------------------- */

    if (configuration.customization == RENAME) {
      const country = input.localization.country.isoCode ?? ''
      const customerTags = []
      input.cart.buyerIdentity?.customer?.hasTags.map((row) => {
        if (row.hasTag == true) {
          customerTags.push(row.tag)
        }
      })

      /**
       * @param {any} methodsArray
       */
      function renamePaymentMethods(methodsArray) {
        // @ts-ignore
        for (let index = 0; index < methodsArray.length; index++) {
          // @ts-ignore
          const methods = methodsArray[index]
          const oldPaymentMethod = input.paymentMethods.find((method) =>
            method.name.includes(methods.old_method),
          )
          const newPaymentMethod = methods.new_method
          if (oldPaymentMethod) {
            allOperations.push({
              rename: {
                name: newPaymentMethod,
                paymentMethodId: oldPaymentMethod.id,
              },
            })
          }
        }
      }
      if (configuration.conditionStatus == [ALWAYS]) {
        for (let index = 0; index < configuration?.fields?.length; index++) {
          const element = configuration?.fields[index]
          renamePaymentMethods(element.methods)
        }
      } else {
        const language = input.localization.language.isoCode ?? ''

        for (let index = 0; index < configuration?.fields?.length; index++) {
          const element = configuration?.fields[index]

          const countryCodeList = element.countries.map(
            (/** @type {{ code: any; }} */ row) => row.code,
          )
          const languageCodeList = element.languages.map(
            (/** @type {{ code: any; }} */ row) => row.code,
          )
          if (countryCodeList.includes(country)) {
            renamePaymentMethods(element.methods)
          }

          if (languageCodeList.includes(language)) {
            renamePaymentMethods(element.methods)
          }

          if (
            customerTags.some((/** @type {any} */ customerTags) =>
              element.customerTags.includes(customerTags),
            )
          ) {
            renamePaymentMethods(element.methods)
          }
        }
      }
    }
    /*  ------------------------------- RENAME END ------------------------------------------- */
  }

  return {
    operations: allOperations,
  }
}
