function popUpDelete(event) {
    let popup= document.getElementById("popup")
    popup.style.display = 'block';
}
function cancelDelete(){
    let popup= document.getElementById("popup")
    popup.style.display = 'none';
}
function deleteEvent(){
    let URL = Routing.generate('deleteAccount');
    let xhr = new XMLHttpRequest();

    xhr.open("DELETE", URL, true);
    console.log("???");
    xhr.onload = function () {
        console.log("success onload")
    };
    xhr.send(null);
}