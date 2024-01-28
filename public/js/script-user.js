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
    xhr.onload = function () {
        console.log("success onload")

        //Fonction déclenchée quand on reçoit la réponse du serveur.
        //xhr.status permet d'accèder au code de réponse HTTP (200, 204, 403, 404, etc...)
    };
    //On exécute la requête
    console.log(xhr.status);
    //On précise null s'il n'y a pas de données supplémentaires (payload) à envoyer.
    xhr.send(null);
}

let buttons = document.getElementsByClassName("delete-feedy");
Array.from(buttons).forEach(function (button) {
    button.addEventListener("click", supprimerFeedy);
});