var toggleElements = document.getElementsByClassName("modal-toggle");
for(var i = 0; i < toggleElements.length; i++){
    toggleElements[i].addEventListener("click", toggle_modal)
}

function toggle_modal(){
    let modalClasses = document.getElementById("modal-delete-warning").classList;
    if( !modalClasses.contains("is-active") ){
        modalClasses.add("is-active");
    } else {
        modalClasses.remove("is-active");
    }
}