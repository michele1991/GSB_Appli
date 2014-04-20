//<![CDATA[

/**
 * Affiche un message en indiquant toute les modifications qui ont eu lieu au niveau forfait
 * Valide le scénario 6 de "valider fiche frais"
 * 
 * @returns void
 */
function afficheMsgInfosForfaitAActualisees() {
    document.getElementById('msgFraisForfait').style.display = "block";
    document.getElementById('actionsFraisForfait').style.display = "inline";
    document.getElementById('lkActualiserLigneFraisForfait').style.display = "inline";
    document.getElementById('lkReinitialiserLigneFraisForfait').style.display = "inline";
}

/**
 * Affiche un message en indiquant toute les modifications qui ont eu lieu au niveau hors forfait
 * @param int idAMontrer
 * @returns void
 */
function afficheMsgInfosHorsForfaitAActualisees(idAMontrer) {
    document.getElementById('msgFraisHorsForfait' + idAMontrer).style.display = "block";
    document.getElementById('lkActualiserLigneFraisHF' + idAMontrer).style.display = "inline";
    document.getElementById('lkReinitialiserLigneFraisHF' + idAMontrer).style.display = "inline";
}

/**
 * Affiche un message en indiquant les modification qui ont eu lieu pour le nombre de justificatifs
 * @returns void
 */
function afficheMsgNbJustificatifs() {
    document.getElementById('msgNbJustificatifs').style.display = "block";
    document.getElementById('lkActualiserNbJustificatifs').style.display = "inline";
    document.getElementById('lkReinitialiserNbJustificatifs').style.display = "inline";
}

/**
 * Réinitialise les modifications qui ont eue lieu au niveau forfait
 * @returns void
 */
function reinitialiserLigneFraisForfait() {
    document.getElementById('formFraisForfait').reset();        
}

/**
 * Réinitialise les modifications qui ont eue lieu au niveau hors forfait
 * @param int idElementHF
 * @returns void
 */
function reinitialiserLigneFraisHorsForfait(idElementHF) {
    document.getElementById('formFraisHorsForfait' + idElementHF).reset();
}

/**
 * Réinitialise les nombre de justificatifs
 * @returns void
 */
function reinitialiserNbJustificatifs() {
    document.getElementById('formNbJustificatifs').reset();
}

/**
 *Premet de changer le visiteur 
 * @param int idVisiteur
 * @returns void
 */
function changerVisiteur(idVisiteur) {
    if(getModifsEnCours()) {
        if(confirm('Attention, des modifications n\'ont pas été actualisées. Souhaitez-vous vraiment changer de visiteur et perdre toutes les modifications non actualisées ?')) {
            if(!idVisiteur) {
                // C'est le bouton "Changer de visiteur" qui a été utilisé
                // On recharge la page comme si on avait cliqué dans le sommaire
                window.location = "./cValidFichesFrais.php";
            } else {
                // On change de visiteur avec le visiteur choisi
                document.getElementById('formChoixVisiteur').submit();
            }
        }
    } else {
        if(!idVisiteur) {
            // C'est le bouton "Changer de visiteur" qui a été utilisé
            // On recharge la page comme si on avait cliqué dans le sommaire
            window.location = "./cValidFichesFrais.php";
        } else {
            // On change de visiteur avec le visiteur choisi
            document.getElementById('formChoixVisiteur').submit();
        }
    }
}

/**
 * 
 * @returns {Boolean}
 */
function getModifsEnCours() {
    var modif = false;
    // Si cet élément existe, c'est que l'on a bien dépassé le stade du choix de visiteur
    if(document.getElementById('msgFraisForfait')) {
        // Modification en cours sur les frais forfaitisés ?
        if(document.getElementById('msgFraisForfait').style.display == "block") {
            modif = true;
            return modif;
        }
        // Modification en cours sur les frais hors forfaits ?
        var forms = document.getElementsByTagName('form');
        for (var cpt = 0; cpt < forms.length; cpt++) {
            var unForm = forms[cpt];
            if (unForm.id) {
                if(unForm.id.search('formFraisHorsForfait') != -1) {
                    if(document.getElementById('msgFraisHorsForfait' + unForm.id.replace('formFraisHorsForfait',"")).style.display == "block") {
                        modif = true;
                        return modif;
                    }
                }
            }   
        }
        // Modification en cours sur le nombre de justificatifs ?
        if(document.getElementById('msgNbJustificatifs').style.display == "block") {
            modif = true;
            return modif;
        }
    }
    return modif
}

/**
 * Cette fonction permet d'actualiser les frais forfaits changé
 * Elle valide le scénario 5 de "Valider fiche frais"
 * 
 * @param char(3) rep
 * @param char(3) nui
 * @param char(3) etp
 * @param char(3) km
 * @returns void
 */
function actualiserLigneFraisForfait(rep,nui,etp,km) {
    // Trouver quelles sont les mises à jour à réaliser
    var modif = false;
    var txtModifs = '';
    if (rep != document.getElementById('idREP').value) {
        // Modification portant sur la date
        modif = true;
        txtModifs += '\n\nAncienne quantité de repas : ' + rep + ' \n \'--> Nouvelle quantité : ' + document.getElementById('idREP').value;
    }
    if (nui != document.getElementById('idNUI').value) {
        // Modification portant sur la date
        modif = true;
        txtModifs += '\n\nAncienne quantité de nuitées : ' + nui + ' \n \'--> Nouvelle quantité : ' + document.getElementById('idNUI').value;
    }
    if (etp != document.getElementById('idETP').value) {
        // Modification portant sur la date
        modif = true;
        txtModifs += '\n\nAncienne quantité d\'étapes : ' + etp + ' \n \'--> Nouvelle quantité : ' + document.getElementById('idETP').value;
    }
    if (km != document.getElementById('idKM').value) {
        // Modification portant sur la date
        modif = true;
        txtModifs += '\n\nAncienne quantité de kilomètres : ' + km + ' \n \'--> Nouvelle quantité : ' + document.getElementById('idKM').value;
    }
    if (modif) {
        var question = 'Souhaitez-vous vraiment effectuer la ou les modifications suivantes cette ligne de frais forfaitisés ?' + txtModifs;
        if (confirm(question)) {
            document.getElementById('formFraisForfait').submit();
        }
    } else {
        alert('Aucune modification à actualiser...');
        reinitialiserLigneFraisForfait();
    }
}

/**
 * Permet d'actualiser la ligne hors forfait
 * 
 * @param int idElementHF
 * @param date dateElementHF
 * @param varchar(100) libelleElementHF
 * @param decimal(10,2) montantElementHF
 * @returns void
 */
function actualiserLigneFraisHF(idElementHF,dateElementHF,libelleElementHF,montantElementHF) {
    // Trouver quelles sont les mises à jour à réaliser
    var modif = false;
    var txtModifs = '';
    if (dateElementHF != document.getElementById('idDate' + idElementHF).value) {
        // Modification portant sur la date
        modif = true;
        txtModifs += '\n\nAncienne date : "' + dateElementHF + '" \n \'--> Nouvelle date : "' + document.getElementById('idDate' + idElementHF).value + '"';
    }
    if (libelleElementHF != document.getElementById('idLibelle' + idElementHF).value) {
        // Modification portant sur le libellé
        modif = true;
        txtModifs += '\n\nAncien libellé : "' + libelleElementHF + '" \n \'--> Nouveau libellé : ' + document.getElementById('idLibelle' + idElementHF).value + '"';
    }
    if (montantElementHF != document.getElementById('idMontant' + idElementHF).value) {
        // Modification portant sur le montant
        modif = true;
        txtModifs += '\n\nAncien montant : ' + montantElementHF + '\u20AC \n \'--> Nouveau montant : ' + document.getElementById('idMontant' + idElementHF).value + '\u20AC';
    }
    // Demande de confirmation s'il y a des modifications à réellement actualiser
    if (modif) {
        var question = 'Souhaitez-vous vraiment effectuer la ou les modifications suivantes cette ligne de frais hors forfait ?' + txtModifs;
        if (confirm(question)) {
            document.getElementById('formFraisHorsForfait' + idElementHF).submit();
        }
    } else {
        alert('Aucune modification à actualiser...');
        reinitialiserLigneFraisHorsForfait(idElementHF);
    }
}

/**
 * Actualise le nombre de justificatifs
 * 
 * @param int nbJustificatifs
 * @returns void
 */
function actualiserNbJustificatifs(nbJustificatifs) {
    if (confirm('Souhaitez-vous vraiment passer le nombre de justificatifs de ' + nbJustificatifs + ' à ' + document.getElementById('idNbJustificatifs').value + ' ?')) {
        document.getElementById('formNbJustificatifs').submit();
    }
}

/**
 * Permet de reporter une ligne hors forfait au mois suivant
 * Valide l'exeption 7-a de "valider fiche frais"
 * @param int idElementHF
 * @returns void
 */
function reporterLigneFraisHF(idElementHF) {
    var question = 'Souhaitez-vous vraiment reporter la ligne de frais hors forfait du ' + document.getElementById('idDate' + idElementHF).value ;
    question += ' portant le libellé "' + document.getElementById('idLibelle' + idElementHF).value + '"';
    question += ' pour un montant de ' + document.getElementById('idMontant' + idElementHF).value + '\u20AC ?';
    if (confirm(question)) {
        // On passe par l'étape "reporterLigneFraisHF"
        document.getElementById('idEtape' + idElementHF).value = 'reporterLigneFraisHF';
        document.getElementById('formFraisHorsForfait' + idElementHF).submit();
    }
}

/**
 * Fonction qui permet de supprimer un frais hors forfait
 * Valide le scénario 7 de "valider fiche frais"
 * 
 * @param int idElementHF
 * @returns void
 */
function refuseLigneFraisHF(idElementHF) {
    var question = 'Souhaitez-vous vraiment supprimer la ligne de frais hors forfait du ' + document.getElementById('idDate' + idElementHF).value ;
    question += ' portant le libellé "' + document.getElementById('idLibelle' + idElementHF).value + '"';
    question += ' pour un montant de ' + document.getElementById('idMontant' + idElementHF).value + '\u20AC ?';
    if (confirm(question)) {
        // On ajoute en début de libelle le texte "REFUSÉ : ", valide le scénario 8 "valider fiche frais"
        document.getElementById('idLibelle' + idElementHF).value = 'REFUSÉ : ' + document.getElementById('idLibelle' + idElementHF).value;
        document.getElementById('formFraisHorsForfait' + idElementHF).submit();
    }
}

/**
 * Fonction permettant de réintégrer un frais hors forfait
 * 
 * @param int idElementHF
 * @returns {undefined}
 */
function reintegrerLigneFraisHF(idElementHF) {
    var question = 'Souhaitez-vous vraiment réintégrer la ligne de frais hors forfait du ' + document.getElementById('idDate' + idElementHF).value ;
    question += ' portant le libellé "' + document.getElementById('idLibelle' + idElementHF).value.replace('REFUSÉ : ',"") + '"';
    question += ' pour un montant de ' + document.getElementById('idMontant' + idElementHF).value + '\u20AC ?';
    if (confirm(question)) {
        // On retire en début de libelle le texte "REFUSÉ : "
        document.getElementById('idLibelle' + idElementHF).value = document.getElementById('idLibelle' + idElementHF).value.replace('REFUSÉ : ',"");
        document.getElementById('formFraisHorsForfait' + idElementHF).submit();
    }
}

/**
 * Cette fonction permet de valider la fiche 
 * Réponde au scénario 9 de "valider fiche frais"
 * 
 * @returns void
 */
function validerFiche() {
    var nbRefus = 0;
    var nbValid = 0;
    var forms = document.getElementsByTagName('form');
    for (var cpt = 0; cpt < forms.length; cpt++) {
        var unForm = forms[cpt];
        if (unForm.id) {
            if(unForm.id.search('formFraisHorsForfait') != -1) {
                if(document.getElementById('idLibelle'+ unForm.id.replace('formFraisHorsForfait',"")).value.search('REFUSÉ : ') != -1) {
                    nbRefus++;
                } else {            
                    nbValid++;
                }
            }
        }   
    }
    // Vérification supplémentaire sur le nombre de justificatifs, qui au minimum doit au moins être égal au nombre de ligne de frais validées
    if ((nbValid) > document.getElementById('idNbJustificatifs').value) {
        alert('Attention, le nombre de justificatifs devrait être au minimum égal au nombre de ligne validées...');
    }
    else {
        var synthese = '\n\n Détails de la validation :';
        synthese += '\n - Refus : ' + nbRefus;
        synthese += '\n - Validation : ' + nbValid;
        if(getModifsEnCours()) {
            if(confirm('Attention, des modifications n\'ont pas été actualisées. Souhaitez-vous vraiment valider cette fiche et perdre toutes les modifications non actualisées ?')) {
                if(confirm('Une fois validée, cette fiche n\'apparaîtra plus dans les fiches à valider et vous ne pourrez plus la modifier. Souhaitez-vous valider tout de même cette fiche ?' + synthese)) {
                    document.getElementById('formValidFiche').submit();
                }
            }
        } else {
            if(confirm('Une fois validée, cette fiche n\'apparaîtra plus dans les fiches à valider et vous ne pourrez plus la modifier. Souhaitez-vous valider tout de même cette fiche ?' + synthese)) {
                document.getElementById('formValidFiche').submit();
            }
        }
    }
}
//]]>
