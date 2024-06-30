<template>
  <div class="common">
    <Grid>
      <GridCell :column-span="{ xs: 6, sm: 6, md: 6, lg: 12, xl: 12 }">
        <LegacyCard
          title="Limitations"
          :sectioned="true"
        >
          <div class="common">
            <List type="bullet">
              <ListItem>
                The Payment Customization API doesn't currently support draft
                orders.
              </ListItem>

              <ListItem>
                You can't rename payment methods that have logos.
              </ListItem>

              <ListItem>
                Payment customizations aren't compatible with Shop Pay (Mobile
                App).
              </ListItem>

              <ListItem>
                You can activate only 5 Payment customization methods at a time.
              </ListItem>

              <ListItem>
                APP do not hide express checkout button, you have to disable it
                from Shopify's settings.
              </ListItem>
            </List>
          </div>

          <div style="margin-top:30px;">
            <Text
              variant="headingSm"
              as="h6"
            >
              Once Shopify will launch the update, we will update our app
              accordingly.
            </Text>
          </div>
        </LegacyCard>
      </GridCell>
    </Grid>
  </div>
</template>

<style scoped>
.common {
  margin: 20px 0 20px 0;
}
</style>
