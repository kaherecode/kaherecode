export function hideTabs() {
    // hide all tabs except the active one
    const tabList = document.getElementsByClassName("tab-item");
    for (let i = 0; i < tabList.length; i++) {
        if (!tabList[i].classList.contains('active')) {
            document.getElementById(tabList[i].getAttribute('data-target')).style.display = "none";
        }
    }
}

export function toggleTab(e) {
    // Get all elements with class="tab-content" and hide them
    const tabs = document.getElementsByClassName("tab-content");
    for (let i = 0; i < tabs.length; i++) {
        tabs[i].style.display = "none";
    }

    // Get all elements with class="tablinks" and remove the class "active"
    const tabLinks = document.getElementsByClassName("tab-item");
    for (let i = 0; i < tabLinks.length; i++) {
        tabLinks[i].className = tabLinks[i].className.replace(" active", "");
    }

    // Show the current tab, and add an "active" class to the button that opened the tab
    document.getElementById(e.currentTarget.getAttribute('data-target')).style.display = "block";
    e.currentTarget.className += " active";
}
