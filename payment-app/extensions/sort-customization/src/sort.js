// @ts-nocheck
import {
  SORT,
  COUNTRY,
  SHIPPING_TITLE,
  CONTAINS,
  NOT_CONTAINS,
} from '../../../../resources/js/constants'
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
  
    /*  ------------------------------- SORT START ------------------------------------------- */

    if (configuration.customization == SORT) {
      
      const country = input.localization.country.isoCode ?? ''
      const shippingTitle =
        input.cart.deliveryGroups[0]?.selectedDeliveryOption?.title

      /* common function */
      function sortPaymentMethods() {
        return (allOperations = configuration.paymentMethods
          .map((/** @type {string} */ methodName, /** @type {any} */ index) => {
            const method = input.paymentMethods.find((m) =>
              m.name.includes(methodName),
            )
            if (method) {
              return {
                move: {
                  index: index,
                  paymentMethodId: method.id,
                },
              }
            }
          })
          .filter((/** @type {any} */ operation) => operation))
      }

      /**
       * @param {{customerDetails: string | any[];}} field
       * @param {string | number | any[]} checkoutvalue
       * @param {string | any[]} countryCodeList
       */
      function checkContains(field, checkoutvalue, countryCodeList) {
        return (
          countryCodeList.includes(checkoutvalue) ||
          field.customerDetails.includes(checkoutvalue)
        )
      }

      /**
       * @param {{customerDetails: string | any[];}} field
       * @param {string | number | any[]} checkoutvalue
       * @param {string | any[]} countryCodeList
       */
      function checkNotContains(field, checkoutvalue, countryCodeList) {
        return (
          (!countryCodeList.includes(checkoutvalue) &&
            countryCodeList.length > 0) ||
          (!field.customerDetails.includes(checkoutvalue) &&
            field.customerDetails.length > 0)
        )
      }

      const cartObject = {
        [COUNTRY]: country,
        [SHIPPING_TITLE]: shippingTitle,
      }

      if (configuration.conditionStatus === 'conditionally') {
        /* Main Database Loop */
        let falseCount = 0
        for (
          let fieldIndex = 0;
          fieldIndex < configuration?.fields?.length;
          fieldIndex++
        ) {
          const element = configuration?.fields[fieldIndex]
          const countryCodeList = element.countries.map(
            (/** @type {{ code: any; }} */ row) => row.code,
          )

          for (const [key, checkoutvalue] of Object.entries(cartObject)) {
            if (key == element.ruleType) {
              let conditionMet = false

              switch (element.ruleCondition) {
                case CONTAINS:
                  conditionMet = checkContains(
                    element,
                    checkoutvalue,
                    countryCodeList,
                  )
                  break
                case NOT_CONTAINS:
                  conditionMet = checkNotContains(
                    element,
                    checkoutvalue,
                    countryCodeList,
                  )
                  break
              }

              if (!conditionMet) {
                // console.log('index:-', fieldIndex, 'is false')
                falseCount++
              }
            }
          }

          /* Check for AND condition */
          if (falseCount == 1) {
            return NO_CHANGES
          }
        }
      }

      if (configuration.sortingValue == 'desc') {
        configuration.paymentMethods.reverse()
      }
      sortPaymentMethods()
    }
    /*  ------------------------------- SORT END ------------------------------------------- */

  }

  return {
    operations: allOperations,
  }
}
