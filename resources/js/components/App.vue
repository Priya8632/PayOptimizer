<template>
  <Suspense>
    <AppProvider>
      <Frame>
        <router-view />
      </Frame>
    </AppProvider>
  </Suspense>
</template>

