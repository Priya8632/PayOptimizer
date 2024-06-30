<template>
  <Layout>
    <LayoutAnnotatedSection
      title="Set Title"
      description=""
    >
      <LegacyCard sectioned>
        <LegacyStack vertical>
          <TextField
            v-model="ruleTitle"
            type="text"
            auto-complete="off"
            required-indicator
          />
        </LegacyStack>
      </LegacyCard>
    </LayoutAnnotatedSection>
  </Layout>

  <Layout>
    <LayoutAnnotatedSection
      title="Customization Rule Status"
      description=""
    >
      <LegacyCard sectioned>
        <LegacyStack vertical>
          <RadioButton
            v-model="ruleStatus"
            value="enabled"
            @change="changeRuleStatus('enabled')"
          >
            <template #label>
              <Badge tone="success">
                Active
              </Badge>
            </template>

            <template #helpText>
              Rule will be enabled on your store, this will affect checkout for
              all customers.
            </template>
          </RadioButton>

          <RadioButton
            v-model="ruleStatus"
            value="disabled"
            @change="changeRuleStatus('disabled')"
          >
            <template #label>
              <Badge>Inactive</Badge>
            </template>

            <template #helpText>
              Disable this rule without deleting it. Deactivated rules will not
              affect checkout for your customers.
            </template>
          </RadioButton>
        </LegacyStack>
      </LegacyCard>
    </LayoutAnnotatedSection>
  </Layout>

  <Divider />
</template>

<script setup>
import { onMounted } from 'vue'
const props = defineProps({
  limitReachedMsg: {
    type: String,
    required: true,
  },
})
const emit = defineEmits('changeRuleStatus')
const ruleTitle = defineModel('ruleTitle')
const ruleStatus = defineModel('ruleStatus')
const changeRuleStatus = (event) => emit('changeRuleStatus', event)

onMounted(() => {
  if (props.limitReachedMsg != '') {
    ruleStatus.value = 'disabled'
  }
})
</script>
