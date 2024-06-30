export const HIDE = 'Hide'
export const RENAME = 'Rename'
export const SORT = 'Sort'

// Common 
export const COUNTRY = 'country'
export const CUSTOMER_TAGS = 'customer-tags'
export const SHIPPING_TITLE = 'shipping-title'

// Hide 
export const TOTAL_AMOUNT = 'total-amount'
export const SUBTOTAL_AMOUNT = 'subtotal-amount'
export const TOTAL_WEIGHT = 'total-weight'
export const TOTAL_QUANTITY = 'total-quantity'
export const SKU = 'sku'
export const COLLECTION = 'collections'
export const ZIP_CODE = 'zip-code'
export const CITY = 'city'
export const TOTAL_MONEY_SPEND = 'total-spend'
export const STATE_CODE = 'state-code'
export const TOTAL_DISCOUNT = 'total-discount'
export const DISCOUNT_RATE = 'discount-rate'
export const SHIPPING_COST = 'shipping-cost'
export const CURRENCY_CODE = 'currency-code'

export const GRAMS = 'GRAMS'
export const KILOGRAMS = 'KILOGRAMS'
export const OUNCES = 'OUNCES'
export const POUNDS = 'POUNDS'

export const GREATER = 'greater than or equals'
export const LESS = 'less than or equals'
export const CONTAINS = 'contains'
export const NOT_CONTAINS = 'not-contains'

// Rename
export const ALWAYS = 'always'
export const LANGUAGE = 'language'

export const toUpperCaseOnFirstLetter = (text) => {
  return text.charAt(0).toUpperCase() + text.slice(1)
}

export const convertWeightUnitText = (text) => {
  if (text == GRAMS) {
    text = 'g.'
  } else if (text == KILOGRAMS) {
    text = 'kg.'
  } else if (text == POUNDS) {
    text = 'lb.'
  } else if (text == OUNCES) {
    text = 'oz.'
  }
  return text
}

export const getDigitValue = (id) => {
  return id.replace(/^\D+/g, '')
}

export const addPaymentMethodsUrl = () => {
  return (
    'https://' +
    'admin.shopify.com/store/' +
    window.auth.name.replace('.myshopify.com', '') +
    '/settings/payments'
  )
}

export const limitReachedUrl = () => {
  return (
    'https://' +
    'admin.shopify.com/store/' +
    window.auth.name.replace('.myshopify.com', '') +
    '/settings/payments/customizations'
  )
}
