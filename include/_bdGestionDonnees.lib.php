<?php

/**
 * Regroupe les fonctions d'accès aux données.
 * @package default
 * @author Arthur Martin
 * @todo RAS
 */

/**
 * Se connecte au serveur de données MySql.                      
 * Se connecte au serveur de données MySql à partir de valeurs
 * prédéfinies de connexion (hôte, compte utilisateur et mot de passe). 
 * Retourne l'identifiant de connexion si succès obtenu, le booléen false 
 * si problème de connexion.
 * @return resource identifiant de connexion
 */
function connecterServeurBD() {
    $hote = "localhost";
    $login = "userGsb";
    $mdp = "secret";
    return mysql_connect($hote, $login, $mdp);
}

/**
 * Sélectionne (rend active) la base de données.
 * Sélectionne (rend active) la BD prédéfinie gsb_valide sur la connexion
 * identifiée par $idCnx. Retourne true si succès, false sinon.
 * @param resource $idCnx identifiant de connexion
 * @return boolean succès ou échec de sélection BD 
 */
function activerBD($idCnx) {
    $bd = "gsb_valide";
    $query = "SET CHARACTER SET utf8";
    // Modification du jeu de caractères de la connexion
    $res = mysql_query($query, $idCnx);
    $ok = mysql_select_db($bd, $idCnx);
    return $ok;
}

/**
 * Ferme la connexion au serveur de données.
 * Ferme la connexion au serveur de données identifiée par l'identifiant de 
 * connexion $idCnx.
 * @param resource $idCnx identifiant de connexion
 * @return void  
 */
function deconnecterServeurBD($idCnx) {
    mysql_close($idCnx);
}

/**
 * Echappe les caractères spéciaux d'une chaîne.
 * Envoie la chaîne $str échappée, càd avec les caractères considérés spéciaux
 * par MySql (tq la quote simple) précédés d'un \, ce qui annule leur effet spécial
 * @param string $str chaîne à échapper
 * @return string chaîne échappée 
 */
function filtrerChainePourBD($str) {
    if (!get_magic_quotes_gpc()) {
        // si la directive de configuration magic_quotes_gpc est activée dans php.ini,
        // toute chaîne reçue par get, post ou cookie est déjà échappée 
        // par conséquent, il ne faut pas échapper la chaîne une seconde fois                              
        $str = mysql_real_escape_string($str);
    }
    return $str;
}

/**
 * Fournit les informations sur un utilisateur demandé. 
 * Retourne les informations de l'utilisateur d'id $unId sous la forme d'un tableau
 * associatif dont les clés sont les noms des colonnes(id, nom, prenom).
 * @param resource $idCnx identifiant de connexion
 * @param string $unId id de l'utilisateur
 * @return array  tableau associatif de l'utilisateur
 */
function obtenirDetailUtilisateur($idCnx, $unId) {
    $id = filtrerChainePourBD($unId);
    $requete = "select utilisateur.id, nom, prenom, libelleType from utilisateur inner join type on utilisateur.idType=type.id where utilisateur.id='" . $unId . "'";
    $idJeuRes = mysql_query($requete, $idCnx);
    $ligne = false;
    if ($idJeuRes) {
        $ligne = mysql_fetch_assoc($idJeuRes);
        mysql_free_result($idJeuRes);
    }
    return $ligne;
}

/**
 * Fournit les informations d'une fiche de frais. 
 * Retourne les informations de la fiche de frais du mois de $unMois (MMAAAA)
 * sous la forme d'un tableau associatif dont les clés sont les noms des colonnes
 * (nbJustitificatifs, idEtat, libelleEtat, dateModif, montantValide).
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demandé (MMAAAA)
 * @param string $unIdVisiteur id visiteur  
 * @return array tableau associatif de la fiche de frais
 */
function obtenirDetailFicheFrais($idCnx, $unMois, $unIdVisiteur) {
    $unMois = filtrerChainePourBD($unMois);
    $ligne = false;
    $requete = "select IFNULL(nbJustificatifs,0) as nbJustificatifs, etat.id as idEtat, libelle as libelleEtat, dateModif, montantValide
    from fichefrais inner join etat on idEtat = etat.id 
    where idVisiteur='" . $unIdVisiteur . "' and mois='" . $unMois . "'";
    $idJeuRes = mysql_query($requete, $idCnx);
    if ($idJeuRes) {
        $ligne = mysql_fetch_assoc($idJeuRes);
    }
    mysql_free_result($idJeuRes);

    return $ligne;
}

/**
 * Vérifie si une fiche de frais existe ou non. 
 * Retourne true si la fiche de frais du mois de $unMois (MMAAAA) du visiteur 
 * $idVisiteur existe, false sinon. 
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demandé (MMAAAA)
 * @param string $unIdVisiteur id visiteur  
 * @return booléen existence ou non de la fiche de frais
 */
function existeFicheFrais($idCnx, $unMois, $unIdVisiteur) {
    $unMois = filtrerChainePourBD($unMois);
    $requete = "select idVisiteur from fichefrais where idVisiteur='" . $unIdVisiteur .
            "' and mois='" . $unMois . "'";
    $idJeuRes = mysql_query($requete, $idCnx);
    $ligne = false;
    if ($idJeuRes) {
        $ligne = mysql_fetch_assoc($idJeuRes);
        mysql_free_result($idJeuRes);
    }

    // si $ligne est un tableau, la fiche de frais existe, sinon elle n'exsite pas
    return is_array($ligne);
}

/**
 * Fournit le mois de la dernière fiche de frais d'un visiteur.
 * Retourne le mois de la dernière fiche de frais du visiteur d'id $unIdVisiteur.
 * @param resource $idCnx identifiant de connexion
 * @param string $unIdVisiteur id visiteur  
 * @return string dernier mois sous la forme AAAAMM
 */
function obtenirDernierMoisSaisi($idCnx, $unIdVisiteur) {
    $requete = "select max(mois) as dernierMois from fichefrais where idVisiteur='" .
            $unIdVisiteur . "'";
    $idJeuRes = mysql_query($requete, $idCnx);
    $dernierMois = false;
    if ($idJeuRes) {
        $ligne = mysql_fetch_assoc($idJeuRes);
        $dernierMois = $ligne["dernierMois"];
        mysql_free_result($idJeuRes);
    }
    return $dernierMois;
}

/**
 * Ajoute une nouvelle fiche de frais et les éléments forfaitisés associés, 
 * Ajoute la fiche de frais du mois de $unMois (MMAAAA) du visiteur 
 * $idVisiteur, avec les éléments forfaitisés associés dont la quantité initiale
 * est affectée à 0. Clôt éventuellement la fiche de frais précédente du visiteur. 
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demandé (MMAAAA)
 * @param string $unIdVisiteur id visiteur  
 * @return void
 */
function ajouterFicheFrais($idCnx, $unMois, $unIdVisiteur) {
    $unMois = filtrerChainePourBD($unMois);
    // modification de la dernière fiche de frais du visiteur
    $dernierMois = obtenirDernierMoisSaisi($idCnx, $unIdVisiteur);
    $laDerniereFiche = obtenirDetailFicheFrais($idCnx, $dernierMois, $unIdVisiteur);
    if (is_array($laDerniereFiche) && $laDerniereFiche['idEtat'] == 'CR') {
        modifierEtatFicheFrais($idCnx, $dernierMois, $unIdVisiteur, 'CL');
    }

    // ajout de la fiche de frais à l'état Créé
    $requete = "insert into fichefrais (idVisiteur, mois, nbJustificatifs, montantValide, idEtat, dateModif) values ('"
            . $unIdVisiteur
            . "','" . $unMois . "',0,NULL, 'CR', '" . date("Y-m-d") . "')";
    mysql_query($requete, $idCnx);

    // ajout des éléments forfaitisés
    $requete = "select id from fraisforfait";
    $idJeuRes = mysql_query($requete, $idCnx);
    if ($idJeuRes) {
        $ligne = mysql_fetch_assoc($idJeuRes);
        while (is_array($ligne)) {
            $idFraisForfait = $ligne["id"];
            // insertion d'une ligne frais forfait dans la base
            $requete = "insert into lignefraisforfait (idVisiteur, mois, idFraisForfait, quantite)
                        values ('" . $unIdVisiteur . "','" . $unMois . "','" . $idFraisForfait . "',0)";
            mysql_query($requete, $idCnx);
            // passage au frais forfait suivant
            $ligne = mysql_fetch_assoc($idJeuRes);
        }
        mysql_free_result($idJeuRes);
    }
}

/**
 * Retourne le texte de la requête select concernant les mois pour lesquels un 
 * visiteur a une fiche de frais. 
 * 
 * La requête de sélection fournie permettra d'obtenir les mois (AAAAMM) pour 
 * lesquels le visiteur $unIdVisiteur a une fiche de frais. 
 * @param string $unIdVisiteur id visiteur  
 * @param string $unEtat (facultatif)
 * @return string texte de la requête select
 */
function obtenirReqMoisFicheFrais($unIdVisiteur, $unEtat = "") {
    if ($unEtat == "") {
        // Utilisation originelle de la fonction
        $req = "select fichefrais.mois as mois from  fichefrais where fichefrais.idvisiteur ='"
                . $unIdVisiteur . "' order by fichefrais.mois desc ";
    } else {
        // On applique une restriction sur l'état
        $req = "select fichefrais.mois as mois from  fichefrais where fichefrais.idvisiteur ='"
                . $unIdVisiteur . "' and fichefrais.idetat = '"
                . $unEtat . "' order by fichefrais.mois desc ";
    }
    return $req;
}

/**
 * Retourne le texte de la requête select concernant les éléments forfaitisés 
 * d'un visiteur pour un mois donnés. 
 * 
 * La requête de sélection fournie permettra d'obtenir l'id, le libellé et la
 * quantité des éléments forfaitisés de la fiche de frais du visiteur
 * d'id $idVisiteur pour le mois $mois    
 * @param string $unMois mois demandé (MMAAAA)
 * @param string $unIdVisiteur id visiteur  
 * @return string texte de la requête select
 */
function obtenirReqEltsForfaitFicheFrais($unMois, $unIdVisiteur) {
    $unMois = filtrerChainePourBD($unMois);
    $requete = "select idFraisForfait, libelle, quantite from lignefraisforfait
              inner join fraisforfait on fraisforfait.id = lignefraisforfait.idFraisForfait
              where idVisiteur='" . $unIdVisiteur . "' and mois='" . $unMois . "'";
    return $requete;
}

/**
 * Retourne le texte de la requête select concernant les éléments hors forfait 
 * d'un visiteur pour un mois donnés. 
 * 
 * La requête de sélection fournie permettra d'obtenir l'id, la date, le libellé 
 * et le montant des éléments hors forfait de la fiche de frais du visiteur
 * d'id $idVisiteur pour le mois $mois    
 * @param string $unMois mois demandé (MMAAAA)
 * @param string $unIdVisiteur id visiteur  
 * @return string texte de la requête select
 */
function obtenirReqEltsHorsForfaitFicheFrais($unMois, $unIdVisiteur) {
    $unMois = filtrerChainePourBD($unMois);
    $requete = "select id, date, libelle, montant from lignefraishorsforfait
              where idVisiteur='" . $unIdVisiteur
            . "' and mois='" . $unMois . "'";
    return $requete;
}


/**
 * Ajoute une nouvelle ligne hors forfait.
 * Insère dans la BD la ligne hors forfait de libellé $unLibelleHF du montant 
 * $unMontantHF ayant eu lieu à la date $uneDateHF pour la fiche de frais du mois
 * $unMois du visiteur d'id $unIdVisiteur
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demandé (AAMMMM)
 * @param string $unIdVisiteur id du visiteur
 * @param string $uneDateHF date du frais hors forfait
 * @param string $unLibelleHF libellé du frais hors forfait 
 * @param double $unMontantHF montant du frais hors forfait
 * @return void
 */
function ajouterLigneHF($idCnx, $unMois, $unIdVisiteur, $uneDateHF, $unLibelleHF, $unMontantHF) {
    $unLibelleHF = filtrerChainePourBD($unLibelleHF);
    $uneDateHF = filtrerChainePourBD(convertirDateFrancaisVersAnglais($uneDateHF));
    $unMois = filtrerChainePourBD($unMois);
    $requete = "insert into lignefraishorsforfait(idVisiteur, mois, date, libelle, montant) 
                values ('" . $unIdVisiteur . "','" . $unMois . "','" . $uneDateHF . "','" . $unLibelleHF . "'," . $unMontantHF . ")";
    mysql_query($requete, $idCnx);
}

/**
 * Modifie les quantités des éléments forfaitisés d'une fiche de frais. 
 * Met à jour les éléments forfaitisés contenus  
 * dans $desEltsForfaits pour le visiteur $unIdVisiteur et
 * le mois $unMois dans la table LigneFraisForfait, après avoir filtré 
 * (annulé l'effet de certains caractères considérés comme spéciaux par 
 *  MySql) chaque donnée   
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois demandé (MMAAAA) 
 * @param string $unIdVisiteur  id visiteur
 * @param array $desEltsForfait tableau des quantités des éléments hors forfait
 * avec pour clés les identifiants des frais forfaitisés 
 * @return void  
 */
function modifierEltsForfait($idCnx, $unMois, $unIdVisiteur, $desEltsForfait) {
    $unMois = filtrerChainePourBD($unMois);
    $unIdVisiteur = filtrerChainePourBD($unIdVisiteur);
    foreach ($desEltsForfait as $idFraisForfait => $quantite) {
        $requete = "update lignefraisforfait set quantite = " . $quantite
                . " where idVisiteur = '" . $unIdVisiteur . "' and mois = '"
                . $unMois . "' and idFraisForfait='" . $idFraisForfait . "'";
        mysql_query($requete, $idCnx);
    }
}

/**
 * Contrôle les informations de connexionn d'un utilisateur.
 * Vérifie si les informations de connexion $unLogin, $unMdp sont ou non valides.
 * Retourne les informations de l'utilisateur sous forme de tableau associatif 
 * dont les clés sont les noms des colonnes (id, nom, prenom, login, mdp)
 * si login et mot de passe existent, le booléen false sinon. 
 * @param resource $idCnx identifiant de connexion
 * @param string $unLogin login 
 * @param string $unMdp mot de passe 
 * @return array tableau associatif ou booléen false 
 */
function verifierInfosConnexion($idCnx, $unLogin, $unMdp) {
    $unLogin = filtrerChainePourBD($unLogin);
    $unMdp = filtrerChainePourBD($unMdp);
    // le mot de passe est crypté dans la base avec la fonction de hachage md5
    $req = "select id, nom, prenom, login, mdp, idType from utilisateur where login='" . $unLogin . "' and mdp='" . $unMdp . "'";
    $idJeuRes = mysql_query($req, $idCnx);
    $ligne = false;
    if ($idJeuRes) {
        $ligne = mysql_fetch_assoc($idJeuRes);
        mysql_free_result($idJeuRes);
    }
    return $ligne;
}

/**
 * Modifie l'état et la date de modification d'une fiche de frais
 *
 * Met à jour l'état de la fiche de frais du visiteur $unIdVisiteur pour
 * le mois $unMois à la nouvelle valeur $unEtat et passe la date de modif à 
 * la date d'aujourd'hui
 * @param resource $idCnx identifiant de connexion
 * @param string $unIdVisiteur 
 * @param string $unMois mois sous la forme aaaamm
 * @param string $unEtat
 * @return void 
 */
function modifierEtatFicheFrais($idCnx, $unMois, $unIdVisiteur, $unEtat) {
    $requete = "update fichefrais set idEtat = '" . $unEtat .
            "', dateModif = now() where idVisiteur ='" .
            $unIdVisiteur . "' and mois = '" . $unMois . "'";
    mysql_query($requete, $idCnx) or die(mysql_error());
}

/**
 * Modifie la montant Valide dans la base de données
 * 
 * @param resource $idCnx identifiant de connexion
 * @param string $unIdVisiteur 
 * @param string $unMois mois sous la forme aaaamm
 * @param int $unResultat resultat du calcul du montant total
 * @return void
 * @author Michèle Schatt
 */
function modifierMontantValide($idCnx, $unMois, $unIdVisiteur, $unResultat) {
    $requete = "update fichefrais set montantValide = '" . $unResultat .
            "', dateModif = now() where idVisiteur ='" .
            $unIdVisiteur . "' and mois = '" . $unMois . "'";
    mysql_query($requete, $idCnx) or die(mysql_error());
}

/**
 * Retourne la requete d'obtention de la liste des visiteurs médicaux
 *
 * Retourne la requête d'obtention de la liste des visiteurs médicaux (id, nom et prenom)
 * @return string $requete
 */
function obtenirReqListeVisiteurs() {
    $requete = "select id, nom, prenom from utilisateur where idType='V' order by nom";
    return $requete;
}

/**
 * Modifie les quantités des éléments non forfaitisés d'une fiche de frais. 
 * Met à jour les éléments non forfaitisés contenus  
 * dans $desEltsHorsForfaits
 * @param resource $idCnx identifiant de connexion
 * @param array $desEltsHorsForfait tableau des éléments hors forfait
 * avec pour clés les identifiants des frais hors forfait
 * @return void  
 */
function modifierEltsHorsForfait($idCnx, $desEltsHorsForfait) {
    foreach ($desEltsHorsForfait as $cle => $val) {
        switch ($cle) {
            case 'id':
                $idFraisHorsForfait = $val;
                break;
            case 'libelle':
                $libelleFraisHorsForfait = $val;
                break;
            case 'date':
                $dateFraisHorsForfait = $val;
                break;
            case 'montant':
                $montantFraisHorsForfait = $val;
                break;
        }
    }
    $requete = "update lignefraishorsforfait"
            . " set libelle = '" . filtrerChainePourBD($libelleFraisHorsForfait) . "',"
            . " date = '" . convertirDateFrancaisVersAnglais($dateFraisHorsForfait) . "',"
            . " montant = " . $montantFraisHorsForfait
            . " where id = " . $idFraisHorsForfait;
    mysql_query($requete, $idCnx);
}

/**
 * Modifie le nombre de justificatifs d'une fiche de frais
 *
 * Met à jour le nombre de justificatifs de la fiche de frais du visiteur $unIdVisiteur pour
 * le mois $unMois à la nouvelle valeur $nbJustificatifs
 * @param resource $idCnx identifiant de connexion
 * @param string $unIdVisiteur 
 * @param string $unMois mois sous la forme aaaamm
 * @param int $nbJustificatifs nombre de justificatifs
 * @return void 
 */
function modifierNbJustificatifsFicheFrais($idCnx, $unMois, $unIdVisiteur, $nbJustificatifs) {
    $requete = "update fichefrais set nbJustificatifs = " . $nbJustificatifs .
            " where idVisiteur ='" . $unIdVisiteur . "' and mois = '" . $unMois . "'";
    mysql_query($requete, $idCnx);
}

/**
 * Reporte la ligne hors forfait au mois suivant 
 * @param resource $idCnx identifiant de connexion
 * @param string $unMois mois sous la forme aaaamm
 * @param string $unIdVisiteur 
 * @param int $unIdLigneHorsForfait id de la ligne horsforfait sélectionnée
 * @return void
 * @author Michèle Schatt
 */
function reporterEltsHorsForfait($idCnx, $unMois, $unIdVisiteur, $unIdLigneHorsForfait) {
    $unMois = filtrerChainePourBD($unMois);
    $unIdVisiteur = filtrerChainePourBD($unIdVisiteur);
    $unIdLigneHorsForfait = filtrerChainePourBD($unIdLigneHorsForfait);
    list($annee, $mois) = sscanf($unMois, "%04d%02d");
 
    // Si le mois en cours est déjà 12 on passe au janvier et on augemente annee de 1
    if($mois >= "12")
    {
        $mois = '01';
        $annee = $annee++;
        $moisComplet = $annee . $mois;
    }
    else
    {
        $mois = $mois + 1;
        // pour tous les mois en dessous de 10, on met 0 à gauche
        $mois =  str_pad($mois, 2, '0', STR_PAD_LEFT);
        $moisComplet = $annee . $mois;
    }
 
    $requete = "UPDATE lignefraishorsforfait SET mois = '" .$moisComplet. "'  WHERE id = '" .$unIdLigneHorsForfait. "'";
 
    $existeFicheFrais = existeFicheFrais($idCnx, $moisComplet, $unIdVisiteur);
 
    // si elle n'existe pas, on la créée avec les éléments frais forfaitisés à 0
    if ( !$existeFicheFrais ) {
 
        ajouterFicheFrais($idCnx, $moisComplet , $unIdVisiteur);
        mysql_query($requete, $idCnx);
    }
    else
    {
      mysql_query($requete, $idCnx);
    }
 
}

/**
 * Cloture les fiches de frais antérieur au mois $unMois
 *
 * Cloture les fiches de frais antérieur au mois $unMois
 * et au besoin, créer une nouvelle de fiche de frais pour le mois courant
 * @param ressource $idCnx identifiant de connexion
  * @param string $unMois mois sous la forme aaaamm
  * @return void 
 * @author Michèle Schatt
 */
function cloturerFichesFrais($idCnx, $unMois) {
    $req = "SELECT idVisiteur, mois FROM fichefrais WHERE idEtat = 'CR' AND CAST(mois AS unsigned) < $unMois ;";
    $idJeuFichesFrais = mysql_query($req, $idCnx);
    while ($lgFicheFrais = mysql_fetch_array($idJeuFichesFrais)) {
        modifierEtatFicheFrais($idCnx, $lgFicheFrais['mois'], $lgFicheFrais['idVisiteur'], 'CL');
        // Vérification de l'existence de la fiche de frais pour le mois courant
        $existeFicheFrais = existeFicheFrais($idCnx, $unMois, $lgFicheFrais['idVisiteur']);
        // si elle n'existe pas, on la crée avec les éléments de frais forfaitisés à 0
        if (!$existeFicheFrais) {
            ajouterFicheFrais($idCnx, $unMois, $lgFicheFrais['idVisiteur']);
        }
    }
}

/**
 * Change l'état MP en RB, fait appel a une procédure stockée
 * 
 * @param ressource $idCnx identifiant de connexion
 * @return void
 * @author Michèle Schatt
 */
function etatRemboursement($idCnx) {
             mysql_query('CALL etat_remboursement();', $idCnx);
        
}
       