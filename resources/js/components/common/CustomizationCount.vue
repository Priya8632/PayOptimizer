<template>
  <div class="common">
    <Grid>
      <GridCell :column-span="{ xs: 6, sm: 6, md: 6, lg: 4, xl: 4 }">
        <Card>
          <p style="padding-bottom: 20px;">
            Active Payment Customizations
          </p>

          <p>
            <span class="active">{{ activeCustomizationCount }}</span>
            /5 Our App active delivery rule(s)
          </p>
        </Card>
      </GridCell>

      <GridCell :column-span="{ xs: 6, sm: 6, md: 6, lg: 4, xl: 4 }">
        <Card>
          <p style="padding-bottom: 20px;">
            Add new customization
          </p>

          <Button
            variant="plain"
            @click.stop.prevent="
              $router.push({
                name: 'create',
              })
            "
          >
            Create Payment Customization
          </Button>
        </Card>
      </GridCell>

      <GridCell :column-span="{ xs: 6, sm: 6, md: 6, lg: 4, xl: 4 }">
        <Card>
          <p style="padding-bottom: 20px;">
            Your activated / created rules count.
          </p>

          <Grid>
            <GridCell :column-span="{ xs: 2, sm: 2, md: 2, lg: 4, xl: 4 }">
              Hide
            </GridCell>

            <GridCell :column-span="{ xs: 2, sm: 2, md: 2, lg: 4, xl: 4 }">
              Sort
            </GridCell>

            <GridCell :column-span="{ xs: 2, sm: 2, md: 2, lg: 4, xl: 4 }">
              Rename
            </GridCell>
          </Grid>

          <Grid>
            <GridCell :column-span="{ xs: 2, sm: 2, md: 2, lg: 4, xl: 4 }">
              <span class="active">
                {{ hideEnableCount }}
              </span>
              {{ '/' + hideCount }}
            </GridCell>

            <GridCell :column-span="{ xs: 2, sm: 2, md: 2, lg: 4, xl: 4 }">
              <span class="active">
                {{ sortEnableCount }}
              </span>
              {{ '/' + sortCount }}
            </GridCell>

            <GridCell :column-span="{ xs: 2, sm: 2, md: 2, lg: 4, xl: 4 }">
              <span class="active">
                {{ renameEnableCount }}
              </span>
              {{ '/' + renameCount }}
            </GridCell>
          </Grid>
        </Card>
      </GridCell>
    </Grid>
  </div>
</template>

<script setup>
const props = defineProps({
  activeCustomizationCount: {
    type: Number,
    required: true,
  },
  hideCount: {
    type: Number,
    required: true,
  },
  renameCount: {
    type: Number,
    required: true,
  },
  sortCount: {
    type: Number,
    required: true,
  },
  hideEnableCount: {
    type: Number,
    required: true,
  },
  sortEnableCount: {
    type: Number,
    required: true,
  },
  renameEnableCount: {
    type: Number,
    required: true,
  },
})
</script>

<style>
.active{
  font-weight: 800;
  font-size: medium;
}
</style>