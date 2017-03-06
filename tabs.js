function changeTab(evt, tabName) {
    var i;
    
    // Hide other tabs
    var tabs = document.getElementsByClassName("tab");
    for (i = 0; i < tabs.length; ++i) {
        tabs[i].className = tabs[i].className.replace(" active", "");
    }

    // Set all other links to non-active
    var tablinks = document.getElementsByClassName("tabLink");
    for (i = 0; i < tablinks.length; ++i) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }

    // Show the current tab and set link active
    document.getElementById(tabName).className += " active";
    evt.currentTarget.className += " active";
}
