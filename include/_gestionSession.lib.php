<?php
/** 
 * Regroupe les fonctions de gestion d'une session utilisateur.
 * @package default
 * @todo  RAS
 */

/** 
 * Démarre ou poursuit une session.                     
 *
 * @return void
 */
function initSession() {
    session_start();
}

/** 
 * Fournit l'id de l'utilisateur connecté. 
 * 
 * Retourne l'id de l'utilisateur, une chaîne vide si pas d'utilisateur connecté. 
 * @return string id de l’utilisateur connecté 
 */
function obtenirIdUserConnecte() {
    $ident="";
    if ( isset($_SESSION["loginUser"]) ) {
        $ident = (isset($_SESSION["idUser"])) ? $_SESSION["idUser"] : '';   
    }  
    return $ident ;
}

/**
 * Conserve en variables session les informations du visiteur connecté
 * 
 * Conserve en variables session l'id $id et le login $login du visiteur connecté
 * @param string id du visiteur
 * @param string login du visiteur
 * @param string type de l'utilisateur 
 * @return void 
 */ 
function affecterInfosConnecte($id, $login, $type) { 
 $_SESSION["idUser"] = $id; 
 $_SESSION["loginUser"] = $login; 
 $_SESSION["typeUser"] = $type; 

}

/** 
 * Déconnecte le visiteur qui s'est identifié sur le site.                     
 *
 * @return void
 */
/** 
 * Déconnecte l'utilisateur qui s'est identifié sur le site. 
 * 
 * @return void 
 */ 
function deconnecterUtilisateur() { 
 unset($_SESSION["idUser"]); 
 unset($_SESSION["loginUser"]); 
 unset($_SESSION["typeUser"]); 
} 


/** 
 * Vérifie si un utilisateur s'est connecté sur le site. 
 * 
 * Retourne true si un utilisateur s'est identifié sur le site, false sinon. 
 * @return boolean échec ou succès 
 */ 
function estUtilisateurConnecte() { 
 return isset($_SESSION["loginUser"]); 
} 

?>