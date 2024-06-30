// @ts-nocheck
import {
  HIDE,
  TOTAL_AMOUNT,
  SUBTOTAL_AMOUNT,
  TOTAL_QUANTITY,
  TOTAL_MONEY_SPEND,
  ZIP_CODE,
  CITY,
  COUNTRY,
  COLLECTION,
  TOTAL_WEIGHT,
  SKU,
  GREATER,
  LESS,
  CONTAINS,
  NOT_CONTAINS,
  GRAMS,
  KILOGRAMS,
  POUNDS,
  OUNCES,
  SHIPPING_TITLE,
  STATE_CODE,
  CUSTOMER_TAGS,
  TOTAL_DISCOUNT,
  DISCOUNT_RATE,
  SHIPPING_COST,
  CURRENCY_CODE,
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
    /*  ------------------------------- HIDE START------------------------------------------- */
    if (configuration.customization == HIDE) {
      const cartTotal = parseFloat(input.cart.cost.totalAmount.amount ?? '0.0')
      const subtotalAmount = parseFloat(
        input.cart.cost.subtotalAmount.amount ?? '0.0',
      )
      const amountSpent = parseFloat(
        input.cart.buyerIdentity?.customer?.amountSpent.amount ?? '0.0',
      )
      const zipCode = input.cart.deliveryGroups[0]?.deliveryAddress?.zip ?? ''
      const city = input.cart.deliveryGroups[0]?.deliveryAddress?.city ?? ''
      const stateCode =
        input.cart.deliveryGroups[0]?.deliveryAddress?.provinceCode ?? ''
      const shippingCost = parseFloat(
        input.cart.deliveryGroups[0]?.selectedDeliveryOption?.cost.amount ??
          '0.0',
      )
      const country = input.localization.country.isoCode ?? ''
      const customerTags = []
      input.cart.buyerIdentity?.customer?.hasTags.map((row) => {
        if (row.hasTag == true) {
          customerTags.push(row.tag)
        }
      })
      const shippingTitle =
        input.cart.deliveryGroups[0]?.selectedDeliveryOption?.title
      const currencyCode = input.cart.cost.totalAmount.currencyCode ?? ''

      let quantity = 0
      let sku = []
      let weight = 0
      let inCollections = []

      /* common function */
      function hidePaymentMethods() {
        return (allOperations = configuration.paymentMethods
          .map((/** @type {string} */ methodName) => {
            const method = input.paymentMethods.find((m) =>
              m.name.includes(methodName),
            )
            if (method) {
              return {
                hide: {
                  paymentMethodId: method.id,
                },
              }
            }
          })
          .filter((/** @type {any} */ operation) => operation))
      }
      /**
       * @param {number} weight
       * @param {any} weightUnit
       */
      function convertToGrams(weight, weightUnit) {
        switch (weightUnit) {
          case 'KILOGRAMS':
            return weight * 1000
          case 'POUNDS':
            return weight * 453.6
          case 'OUNCES':
            return weight * 28.35
        }
      }
      /**
       * @param {number} weight
       * @param {any} weightUnit
       */
      function convertToKilograms(weight, weightUnit) {
        switch (weightUnit) {
          case 'GRAMS':
            return weight / 1000
          case 'POUNDS':
            return weight / 2.205
          case 'OUNCES':
            return weight / 35.274
        }
      }
      /**
       * @param {number} weight
       * @param {any} weightUnit
       */
      function convertToPounds(weight, weightUnit) {
        switch (weightUnit) {
          case 'GRAMS':
            return weight / 453.6
          case 'KILOGRAMS':
            return weight * 2.205
          case 'OUNCES':
            return weight / 16
        }
      }
      /**
       * @param {number} weight
       * @param {any} weightUnit
       */
      function convertToOunces(weight, weightUnit) {
        switch (weightUnit) {
          case 'GRAMS':
            return weight / 28.35
          case 'KILOGRAMS':
            return weight * 35.274
          case 'POUNDS':
            return weight * 16
        }
      }
      /**
       * @param {string | any[]} collection
       * @param {any} inAnyCollection
       */
      function productCollectionList(collection, inAnyCollection) {
        for (let index = 0; index < collection.length; index++) {
          const id = collection[index]
          inCollections.push({
            id: id.collectionId,
            isMember: id.isMember,
            inAnyCollection: inAnyCollection,
          })
        }
      }

      let totalDiscount = 0
      let discountRate = 0
      let productsSubTotal = 0
      let getProductSubTotal = 0

      /* Product Details loop from checkout */
      for (let index = 0; index < input.cart.lines.length; index++) {
        const element = input.cart.lines[index]

        productsSubTotal += parseFloat(
          element.cost.subtotalAmount.amount ?? '0.0',
        )
        if (
          parseFloat(element.cost.subtotalAmount.amount ?? '0.0') !==
          parseFloat(element.cost.totalAmount.amount ?? '0.0')
        ) {
          getProductSubTotal += parseFloat(
            element.cost.subtotalAmount.amount ?? '0.0',
          )
        }
        quantity += element.quantity
        if (element.merchandise.sku != '') {
          sku.push(element.merchandise.sku)
        }

        const dbWeightUnit = configuration.weightUnit // default set grams
        const weightUnit = element.merchandise.weightUnit
        if (dbWeightUnit != weightUnit) {
          if (dbWeightUnit == GRAMS) {
            weight += convertToGrams(element.merchandise.weight, weightUnit)
          } else if (dbWeightUnit == KILOGRAMS) {
            weight += convertToKilograms(element.merchandise.weight, weightUnit)
          } else if (dbWeightUnit == POUNDS) {
            weight += convertToPounds(element.merchandise.weight, weightUnit)
          } else if (dbWeightUnit == OUNCES) {
            weight += convertToOunces(element.merchandise.weight, weightUnit)
          }
        } else {
          weight += element.merchandise.weight
        }

        productCollectionList(
          element.merchandise.product.inCollections,
          element.merchandise.product.inAnyCollection,
        )
      }

      totalDiscount = productsSubTotal - subtotalAmount
      discountRate = isNaN((totalDiscount / getProductSubTotal) * 100)
        ? 0
        : (totalDiscount / getProductSubTotal) * 100

      const cartObject = {
        [TOTAL_AMOUNT]: cartTotal,
        [SUBTOTAL_AMOUNT]: subtotalAmount,
        [TOTAL_MONEY_SPEND]: amountSpent,
        [ZIP_CODE]: zipCode,
        [CITY]: city,
        [COUNTRY]: country,
        [TOTAL_QUANTITY]: quantity,
        [SKU]: sku,
        [TOTAL_WEIGHT]: weight,
        [STATE_CODE]: stateCode,
        [CUSTOMER_TAGS]: customerTags,
        [SHIPPING_TITLE]: shippingTitle,
        [TOTAL_DISCOUNT]: totalDiscount,
        [DISCOUNT_RATE]: discountRate,
        [SHIPPING_COST]: shippingCost,
        [CURRENCY_CODE]: currencyCode,
      }

      /**
       * @param {{customerDetails: string | any[];}} field
       * @param {any[]} sku
       * @param {string | number | any[]} checkoutvalue
       * @param {string | any[]} countryCodeList
       * @param {any[] | undefined} [customerTags]
       */
      function checkContains(
        field,
        sku,
        checkoutvalue,
        countryCodeList,
        customerTags,
      ) {
        return (
          field.customerDetails.includes(checkoutvalue) ||
          countryCodeList.includes(checkoutvalue) ||
          sku.some((/** @type {any} */ sku) =>
            field.customerDetails.includes(sku),
          ) ||
          customerTags.some((/** @type {any} */ customerTags) =>
            field.customerDetails.includes(customerTags),
          )
        )
      }

      /**
       * @param {{customerDetails: string | any[];}} field
       * @param {any[]} sku
       * @param {string | number | any[]} checkoutvalue
       * @param {string | any[]} countryCodeList
       * @param {any[] | undefined} [customerTags]
       */
      function checkNotContains(
        field,
        sku,
        checkoutvalue,
        countryCodeList,
        customerTags,
      ) {
        return (
          (!field.customerDetails.includes(checkoutvalue) &&
            (field.ruleType == [ZIP_CODE] ||
              field.ruleType == [CITY] ||
              field.ruleType == [STATE_CODE] ||
              field.ruleType == [SHIPPING_TITLE] ||
              field.ruleType == [CURRENCY_CODE])) ||
          (!countryCodeList.includes(checkoutvalue) &&
            field.ruleType == [COUNTRY]) ||
          (!sku.some((/** @type {any} */ sku) =>
            field.customerDetails.includes(sku),
          ) &&
            field.ruleType == [SKU]) ||
          (!customerTags.some((/** @type {any} */ customerTags) =>
            field.customerDetails.includes(customerTags),
          ) &&
            field.ruleType == [CUSTOMER_TAGS])
        )
      }

      /**
       * @param {{ cartDetails: number; }} field
       * @param {string | number | any[]} checkoutvalue
       */
      function checkLess(field, checkoutvalue) {
        return checkoutvalue <= field.cartDetails
      }

      /**
       * @param {{ cartDetails: number; }} field
       * @param {string | number | any[]} checkoutvalue
       */
      function checkGreater(field, checkoutvalue) {
        return checkoutvalue >= field.cartDetails
      }

      /* Main Database Loop */
      let falseCount = 0
      for (
        let fieldIndex = 0;
        fieldIndex < configuration?.fields?.length;
        fieldIndex++
      ) {
        const element = configuration.fields[fieldIndex]
        const countryCodeList = element.countries.map(
          (/** @type {{ code: any; }} */ row) => row.code,
        )

        let validCollection = []
        if (element.ruleType == COLLECTION) {
          element.selectedCollectionIds.forEach((
            /** @type {any} */ dbCollectionId,
          ) => {
            inCollections.forEach((collection) => {
              if (dbCollectionId === collection.id) {
                if (
                  element.ruleCondition === CONTAINS &&
                  collection.isMember &&
                  collection.inAnyCollection
                ) {
                  validCollection.push('contain')
                } else if (
                  element.ruleCondition === NOT_CONTAINS &&
                  !collection.isMember &&
                  !collection.inAnyCollection
                ) {
                  validCollection.push('not_contain')
                }
              }
            })
          })

          validCollection.forEach((validation) => {
            if (
              configuration.conditionStatus === 'hide' &&
              (validation === 'contain' || validation === 'not_contain')
            ) {
              hidePaymentMethods()
            }
          })

          /* if not found any true product collection */
          if (validCollection.length == 0) {
            falseCount++
          }
        }

        for (const [key, checkoutvalue] of Object.entries(cartObject)) {
          if (key == element.ruleType) {
            let conditionMet = false

            switch (element.ruleCondition) {
              case CONTAINS:
                conditionMet = checkContains(
                  element,
                  sku,
                  checkoutvalue,
                  countryCodeList,
                  customerTags,
                )
                break
              case NOT_CONTAINS:
                conditionMet = checkNotContains(
                  element,
                  sku,
                  checkoutvalue,
                  countryCodeList,
                  customerTags,
                )
                break
              case LESS:
                conditionMet = checkLess(element, checkoutvalue)
                break
              case GREATER:
                conditionMet = checkGreater(element, checkoutvalue)
                break
            }

            if (conditionMet) {
              // console.log('index:-', fieldIndex, 'is true')
              if (configuration.conditionStatus === 'hide') {
                hidePaymentMethods()
              }
            } else {
              // console.log('index:-', fieldIndex, 'is false')
              falseCount++
            }
          }
        }

        //   console.log(falseCount, '--- final false scount')

        /* Check for AND condition */
        if (configuration.operator == 'and') {
          /* If any one condition false */
          if (falseCount == 1) {
            /* If false then toggle */
            if (configuration.conditionStatus == 'show') {
              hidePaymentMethods()
            } else {
              return NO_CHANGES
            }
          }
        } else {
          /* Check for OR condition*/
          /* If all condition false */
          if (falseCount === configuration.fields.length) {
            /* If false then toggle */
            if (configuration.conditionStatus == 'show') {
              hidePaymentMethods()
            }
          }
        }
      }
    }
    /* -------------------------------------- HIDE END---------------------------------------- */
  }

  return {
    operations: allOperations,
  }
}
