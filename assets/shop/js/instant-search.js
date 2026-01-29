const instantSearchEnabled = window.instantSearchEnabled
const instantSearchPathPlaceholder = window.instantSearchPathPlaceholder
const instantSearchPathHavingPlaceholder = window.instantSearchPath

if (instantSearchEnabled) {

  const forms = document.querySelectorAll('[data-instant-search="form"]')

  forms.forEach((form) => {

    const input = form.querySelector('[data-instant-search="input"]')
    const dropdown = form.querySelector('[data-instant-search="dropdown"]')

    if (!input || !dropdown) return

    let controller = null // per abort fetch precedente

    input.addEventListener('keyup', async function () {

      const value = input.value.trim()

      hideDropdown()

      if (value.length < 3) {
        return
      }

      const instantSearchPath = instantSearchPathHavingPlaceholder.replace(
        instantSearchPathPlaceholder,
        encodeURIComponent(value)
      )

      dropdown.innerHTML = ''

      // abort fetch precedente se typing veloce
      if (controller) {
        controller.abort()
      }
      controller = new AbortController()

      try {

        const response = await fetch(instantSearchPath, {
          signal: controller.signal
        })

        const html = await response.text()

        dropdown.innerHTML = html
        showDropdown()

      } catch (e) {
        if (e.name !== 'AbortError') {
          console.error(e)
        }
        hideDropdown()
      }

    })

    // chiudi su ESC
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        hideDropdown()
      }
    })

    // click fuori → chiudi
    document.addEventListener('click', (e) => {
      if (!form.contains(e.target)) {
        hideDropdown()
      }
    })

    function showDropdown() {
      dropdown.style.display = 'block'
      dropdown.classList.add('show')
      input.setAttribute('aria-expanded', 'true')
    }

    function hideDropdown() {
      dropdown.style.display = 'none'
      dropdown.classList.remove('show')
      input.setAttribute('aria-expanded', 'false')
    }

  })

}
