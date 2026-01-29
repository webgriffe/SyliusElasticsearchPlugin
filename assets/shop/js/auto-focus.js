// Autofocus on search input when mobile search bar is expanded
document
  .getElementById('mobileSearchBar')
  .addEventListener('shown.bs.collapse', () => {
    document.querySelector('#mobileSearchBar input')?.focus()
  })
