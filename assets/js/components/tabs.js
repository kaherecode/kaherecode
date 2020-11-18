function toggleTab(e) {
  // Get the direct tabs parent
  const parentTabs = e.currentTarget.closest('.tabs')

  // Get all elements with class="tab-content" and hide them
  const tabs = document.querySelectorAll(`#${parentTabs.id} > .tab-content`)
  for (let i = 0; i < tabs.length; i++) {
    tabs[i].style.display = 'none'
  }

  // Get all elements with class="tablinks" and remove the class "active"
  const tabLinks = document.querySelectorAll(
    `#${parentTabs.id} > .tab-list > .tab-item`
  )
  for (let i = 0; i < tabLinks.length; i++) {
    tabLinks[i].className = tabLinks[i].className.replace(' active', '')
  }

  // Show the current tab, and add an "active" class to the button that opened the tab
  document.getElementById(
    e.currentTarget.getAttribute('data-target')
  ).style.display = 'block'
  e.currentTarget.className += ' active'
}

// hide all tab-content except the active one
const tabList = document.getElementsByClassName('tab-item')
for (let i = 0; i < tabList.length; i++) {
  if (!tabList[i].classList.contains('active')) {
    document.getElementById(
      tabList[i].getAttribute('data-target')
    ).style.display = 'none'
  }
}

// add click event listener on tab-item elements
document.querySelectorAll('.tab-item').forEach((tab) => {
  tab.addEventListener('click', (event) => {
    toggleTab(event)
  })
})
