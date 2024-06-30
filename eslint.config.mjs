import pluginVue from 'eslint-plugin-vue'
export default [
  // add more generic rulesets here, such as:
  // js.configs.recommended,
  ...pluginVue.configs['flat/recommended'],
  {
    ignores: ['public/js/*'],
  },
  {
    rules: {
      // override/add rules settings here, such as:
      'vue/no-unused-vars': 'error',
      'vue/multi-word-component-names': 'off',
      'vue/no-mutating-props': [
        'error',
        {
          shallowOnly: true,
        },
      ],
      'vue/require-explicit-emits': [
        'error',
        {
          allowProps: true,
        },
      ],
      'vue/require-prop-types': 'off',
      'vue/define-macros-order': [
        'error',
        {
          order: ['defineProps', 'defineEmits', 'defineModel'],
        },
      ],
      'vue/no-useless-mustaches': ['error'],
      'vue/no-useless-v-bind': ['error'],
      'vue/padding-line-between-blocks': ['error', 'always'],
      'vue/padding-line-between-tags': [
        'error',
        [{ blankLine: 'always', prev: '*', next: '*' }],
      ],
      'vue/require-macro-variable-name': [
        'error',
        {
          defineProps: 'props',
          defineEmits: 'emit',
        },
      ],
      'vue/v-on-style': ['error', 'shorthand'],
    },
  },
]
