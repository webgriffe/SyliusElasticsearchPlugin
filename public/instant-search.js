const searchInputElements = document.querySelectorAll('[data-search-input-element]')
const resultContent = document.querySelector('[data-instant-search-result-content]')
const instantSearchEnabled = window.instantSearchEnabled
const instantSearchPathPlaceholder = window.instantSearchPathPlaceholder
const instantSearchPathHavingPlaceholder = window.instantSearchPath

if (instantSearchEnabled) {
  for (const searchInputElement of searchInputElements) {
    searchInputElement.addEventListener('keyup', function () {
      const searchInputValue = searchInputElement.value
      resultContent.classList.remove('active')
      if (searchInputValue.length < 3) {
        return
      }
      const instantSearchPath = instantSearchPathHavingPlaceholder.replace(instantSearchPathPlaceholder, searchInputValue)
      resultContent.innerHTML = ''

      fetch(new Request(instantSearchPath))
        .then((response) => response.text())
        .then((html) => {
          resultContent.innerHTML = html
          resultContent.classList.add('active')
        })
        .catch(e => {
          console.error(e)
          resultContent.classList.remove('active')
        })
    })
  }
}
