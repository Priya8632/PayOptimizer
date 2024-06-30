export const toster = (msg, error) => {
  const toastOptions = {
    message: msg ? msg : '',
    duration: 3000,
    isError: error ? error : false,
  }
  const toastNotice = actions.Toast.create(app, toastOptions)
  return toastNotice.dispatch(actions.Toast.Action.SHOW)
}
