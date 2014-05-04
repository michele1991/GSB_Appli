<?php
/**
 * Script de contrôle et d'affichage du cas d'utilisation "Suivi de paiement fiche de frais"
 * 
 * @package default
 * @todo  RAS
 */
$repInclude = './include/';
require($repInclude . "_init.inc.php");

// page inaccessible si utilisateur non connecté
if (!estUtilisateurConnecte()) {
    header("Location: cSeConnecter.php");
}
require($repInclude . "_entete.inc.html");
require($repInclude . "_sommaire.inc.php");

// acquisition des données entrées, ici l'id de visiteur, le mois, l'étape du traitement et le montant valide de fiche frais
$visiteurChoisi = lireDonnee("lstVisiteur", "");
$moisChoisi = lireDonnee("lstMois", "");
$etape = lireDonnee("etape", "");
$resultat = lireDonnee("montantValide", 0);

// structure de décision sur les différentes étapes du cas d'utilisation
if ($etape == "choixVisiteur") {
    // L'utilisateur a choisi un visiteur
} elseif ($etape == "choixMois") {
    // L'utilisateur a choisi un mois
} elseif ($etape == "mettreEnPaiement") {
    // L'utilisateur valide la fiche l'état passe à "Mise en paiement" pour le scénario 6 de "Suivre le paiement fiche frais"
    modifierEtatFicheFrais($idConnexion, $moisChoisi, $visiteurChoisi, 'MP');
    // Le montant valide est mise à jour dans les bdd avec le montant calculé
    modifierMontantValide($idConnexion, $moisChoisi, $visiteurChoisi, $resultat);
    // mets l'état MP en RB valide l'exception 6-a, fait appel à une procédure stockée
    etatRemboursement($idConnexion);
}
    
?>

<!-- Division principale -->
<div id="contenu">
    <h1>Suivi des paiement des fiches de frais</h1>
    <?php
    if ($etape == "mettreEnPaiement") {
        $lgVisiteur = obtenirDetailUtilisateur($idConnexion, $visiteurChoisi);
        ?>
        <p class="info">La fiche de frais du visiteur <?php echo $lgVisiteur['prenom'] . " " . $lgVisiteur['nom']; ?>
            pour <?php echo obtenirLibelleMois(intval(substr($moisChoisi, 4, 2))) . " " . intval(substr($moisChoisi, 0, 4)); ?> 
            a bien été enregistrée
        </p>        
        <?php
        // On réinitialise le mois choisi pour forcer la disparition du bas de page, la réactualisation des mois et le choix d'un nouveau mois
        $moisChoisi = "";
    }
    ?>
        
    <!--Choix du visiteur répondant au scénario 2 de "Suivre le paiement fiche frais"-->
    <form id="formChoixVisiteur" method="post" action="">
        <p>
            <input type="hidden" name="etape" value="choixVisiteur" />
            <label class="titre">Choisir le visiteur :</label>
            <select name="lstVisiteur" id="idLstVisiteur" class="zone" onchange="changerVisiteur(this.options[this.selectedIndex].value);">
                <?php
                // Si aucun visiteur n'a encore été choisi, on place en premier une invitation au choix
                if ($visiteurChoisi == "") {
                    ?>
                    <option value="-1">Veuillez choisir un visiteur médical</option>
                    <?php
                }
                // On propose tous les utilisateurs qui sont des visteurs médicaux
                $req = obtenirReqListeVisiteurs();
                $idJeuVisiteurs = mysql_query($req, $idConnexion);
                while ($lgVisiteur = mysql_fetch_array($idJeuVisiteurs)) {
                    ?>
                    <option value="<?php echo $lgVisiteur['id']; ?>"<?php if ($visiteurChoisi == $lgVisiteur['id']) { ?> 
                            selected="selected"<?php } ?>><?php echo $lgVisiteur['nom'] . " " . $lgVisiteur['prenom']; ?>
                    </option>
                    <?php
                }
                mysql_free_result($idJeuVisiteurs);
                ?>
            </select>
        </p>
    </form>
    <?php
    // Si aucun visiteur n'a encore été choisi on n'affiche pas le form de choix de mois
    if ($visiteurChoisi != "") {
        ?>
    
        <!--Choix du visiteur répondant au scénario 2 de "Suivre le paiement fiche frais"-->
        <form id="formChoixMois" method="post" action="">
            <p>
                <input type="hidden" name="etape" value="choixMois" />
                <input type="hidden" name="lstVisiteur" value="<?php echo $visiteurChoisi; ?>" />
                
                <?php
                // on met en paramètre l'id de l'état (pour l'instant qu'une par visiteur)
                // si on met rien tout les fiches de tout les visiteur seront affiché
                $req = obtenirReqMoisFicheFrais($visiteurChoisi, 'VA');
                $idJeuMois = mysql_query($req, $idConnexion);
                $lgMois = mysql_fetch_assoc($idJeuMois);
                // 4-a Aucune fiche de frais n'existe le système affiche "Pas de fiche de frais pour ce visiteur ce mois". Retour au 2
                if (empty($lgMois)) {
                    ajouterErreur($tabErreurs, "Pas de fiche de frais à valider pour ce visiteur, veuillez choisir un autre visiteur");
                    echo toStringErreurs($tabErreurs);
                } else {
                    ?>
                    <label class = "titre">Mois :</label>
                    <select name="lstMois" id="idDateValid" class="zone" onchange="this.form.submit();">
                        <?php
                        // Si aucun mois n'a encore été choisi, on place en premier une invitation au choix
                        if ($moisChoisi == "") {
                            ?>
                            <option value="-1">Veuillez choisir un mois</option>
                            <?php
                        }
                        while (is_array($lgMois)) {
                            $mois = $lgMois["mois"];
                            $noMois = intval(substr($mois, 4, 2));
                            $annee = intval(substr($mois, 0, 4));
                            ?>    
                            <option value="<?php echo $mois; ?>"<?php if ($moisChoisi == $mois) { ?> selected="selected"<?php } ?>><?php echo obtenirLibelleMois($noMois) . ' ' . $annee; ?></option>
                            <?php
                            $lgMois = mysql_fetch_assoc($idJeuMois);
                        }
                        mysql_free_result($idJeuMois);
                    }
                    ?>            
                </select>
            </p>        
        </form>
        <?php
    }
    // Valide le scénario 3 et 4 de "suivre le paiement fiche frais" il n'y a pas de boutton valider, l'affichage se fait automatiquement
    //si un visiteur et un mois est choisi pas besoin de mettre $visiteurChoisi != "" &&  comme dans tout les cas
    // le mois peut être choisi que si un visiteur a déjà était sélectionné
    if ($moisChoisi != "") {
        // Traitement des frais si un visiteur et un mois ont été choisis
        $req = obtenirReqEltsForfaitFicheFrais($moisChoisi, $visiteurChoisi);
        $idJeuEltsForfait = mysql_query($req, $idConnexion);
        $lgEltsForfait = mysql_fetch_assoc($idJeuEltsForfait);
        //Valide le scénario 4 de "Suivre le paiement" affichage des frais
        while (is_array($lgEltsForfait)) {
            // On place la bonne valeur en fonction de l'identifiant de forfait
            switch ($lgEltsForfait['idFraisForfait']) {
                case "ETP":
                    $etp = $lgEltsForfait['quantite'];
                    break;
                case "KM":
                    $km = $lgEltsForfait['quantite'];
                    break;
                case "NUI":
                    $nui = $lgEltsForfait['quantite'];
                    break;
                case "REP":
                    $rep = $lgEltsForfait['quantite'];
                    break;
            }
            $lgEltsForfait = mysql_fetch_assoc($idJeuEltsForfait);
            
        }
        mysql_free_result($idJeuEltsForfait);
        $resultat = (($rep *25) + ($etp * 110) + ($km * 0.62) + ($nui * 80)) ;
        ?>
        <form id="formFraisForfait" method="post" action="">
            <p>
                <input type="hidden" name="etape" value="actualiserFraisForfait" />
                <input type="hidden" name="lstVisiteur" value="<?php echo $visiteurChoisi; ?>" />
                <input type="hidden" name="lstMois" value="<?php echo $moisChoisi; ?>" />
            </p>
            <div style="clear:left;"><h2>Frais au forfait</h2></div>
            <table style="color:white;" border="1">
                <tr><th>Repas midi</th><th>Nuitée </th><th>Etape</th><th>Km </th></tr>
                <tr align="center">
                    <!--Si il y a un changement un message s'affiche, valide le scénario 6 de "valider fiche frais"-->
                    <td style="width:80px;">
                        <input type="text" size="3" readonly="readonly" id="idREP" style="border:0px;" name="txtEltsForfait[REP]" value="<?php echo $rep; ?>" 
                               style="text-align:right;" />
                    </td>
                    <td style="width:80px;">
                        <input type="text" size="3" readonly="readonly" id="idNUI" style="border:0px;" name="txtEltsForfait[NUI]" value="<?php echo $nui; ?>" 
                               style="text-align:right;" />
                    </td> 
                    <td style="width:80px;">
                        <input type="text" size="3" readonly="readonly" id="idETP" style="border:0px;" name="txtEltsForfait[ETP]" value="<?php echo $etp; ?>" 
                               style="text-align:right;" />
                    </td>
                    <td style="width:80px;">
                        <input type="text" size="3" readonly="readonly" id="idKM" style="border:0px;" name="txtEltsForfait[KM]" value="<?php echo $km; ?>" 
                               style="text-align:right;" />
                    </td>
                </tr>
            </table>
        </form>
        <p class="titre">&nbsp;</p>
        
        
        <div style="clear:left;"><h2>Hors forfait</h2></div>
        <?php
        // On récupère les lignes hors forfaits
        $req = obtenirReqEltsHorsForfaitFicheFrais($moisChoisi, $visiteurChoisi);
        $idJeuEltsHorsForfait = mysql_query($req, $idConnexion);
        $lgEltsHorsForfait = mysql_fetch_assoc($idJeuEltsHorsForfait);
        
        // on vérifie si un hors forfait éxiste
        if (empty($lgEltsHorsForfait)) {
            ?>
        <p class="info">Pas de hors forfait pour ce visiteur</p>
        <?php
        } else {
                    
            while (is_array($lgEltsHorsForfait)) {
                
                ?>
                <form id="formFraisHorsForfait<?php echo $lgEltsHorsForfait['id']; ?>" method="post" action="">
                    <p>
                        <input type="hidden" id="idEtape<?php echo $lgEltsHorsForfait['id']; ?>" name="etape" value="actualiserFraisHorsForfait" />
                        <input type="hidden" name="lstVisiteur" value="<?php echo $visiteurChoisi; ?>" />
                        <input type="hidden" name="lstMois" value="<?php echo $moisChoisi; ?>" />
                        <input type="hidden" name="txtEltsHorsForfait[id]" value="<?php echo $lgEltsHorsForfait['id']; ?>" />
                    </p>
                    <table style="color:white;" border="1">
                        <tr><th>Date</th><th>Libellé </th><th>Montant</th></tr>
                        <?php
                        // Si les frais n'ont pas déjà été refusés, on affiche normalement
                        if (strpos($lgEltsHorsForfait['libelle'], 'REFUSÉ : ') === false) {
                            $montantHF = $lgEltsHorsForfait['montant'];
                            
                            $resultat = $resultat + $montantHF;
                            ?>
                            <tr>
                                <?php
                            }
                            // Sinon on met la ligne en grisée
                            else {
                                ?>
                            <tr style="background-color:gainsboro;">
                                <?php
                            }
                            ?>                          
                            <td style="width:100px;">
                                <input type="text" size="12" readonly="readonly" id="idDate<?php echo $lgEltsHorsForfait['id']; ?>" style="border:0px;" 
                                       name="txtEltsHorsForfait[date]" value="<?php echo convertirDateAnglaisVersFrancais($lgEltsHorsForfait['date']); ?>" />
                            </td>
                            <td style="width:220px;">
                                <input type="text" size="30" readonly="readonly" id="idLibelle<?php echo $lgEltsHorsForfait['id']; ?>" style="border:0px;"
                                       name="txtEltsHorsForfait[libelle]" value="<?php echo filtrerChainePourNavig($lgEltsHorsForfait['libelle']); ?>"  />
                            </td> 
                            <td style="width:90px;">
                                <input type="text" size="10" readonly="readonly" id="idMontant<?php echo $lgEltsHorsForfait['id']; ?>" style="border:0px;"
                                       name="txtEltsHorsForfait[montant]" value="<?php echo $lgEltsHorsForfait['montant']; ?>" style="text-align:right;"  />
                            </td>
                        </tr>
                    </table>
                </form>
                <?php
                $lgEltsHorsForfait = mysql_fetch_assoc($idJeuEltsHorsForfait);
            }
        }
        ?>
            
        <form id="formNbJustificatifs" method="post" action="">
            <p>
                <input type="hidden" name="etape" value="actualiserNbJustificatifs" />
                <input type="hidden" name="lstVisiteur" value="<?php echo $visiteurChoisi; ?>" />
                <input type="hidden" name="lstMois" value="<?php echo $moisChoisi; ?>" />
            </p>
            <div class="titre">Nombre de justificatifs :
                <?php
                $laFicheFrais = obtenirDetailFicheFrais($idConnexion, $moisChoisi, $visiteurChoisi);
                ?>
                <input type="text" class="zone" size="4" readonly="readonly" id="idNbJustificatifs" name="nbJustificatifs" style="border:0px;"
                       value="<?php echo $laFicheFrais['nbJustificatifs']; ?>" style="text-align:center;" />
            </div>
        </form>
        
        <!--Total du montant à remboursé-->
        <form id="formMontantTotal" method="post" action="">
            <p>
                <input type="hidden" name="montantValide" value="montantTotal" />
                <input type="hidden" name="lstVisiteur" value="<?php echo $visiteurChoisi; ?>" />
                <input type="hidden" name="lstMois" value="<?php echo $moisChoisi; ?>" />
            </p>
            <div class="titre">
                Montant Total :
 
                <input type="text" class="zone" size="20" readonly="readonly" id="idMontantValide" name="montantValide" style="border:0px;"
                       value="<?php echo $resultat; ?> €" style="text-align:center;" />
                
            </div>
        </form>

        <!--affiche le boutton mise en paiement pour valider le scénario 5 de "suivre le paiement fiche frais"-->
        <form id="formMettreEnPaiement" method="post" action="">
            <p>
                <input type="hidden" name="etape" value="mettreEnPaiement" />
                <input type="hidden" name="montantValide" value="<?php echo $resultat; ?>" />
                <input type="hidden" name="lstVisiteur" value="<?php echo $visiteurChoisi; ?>" />
                <input type="hidden" name="lstMois" value="<?php echo $moisChoisi; ?>" />
            <p>
                <a class="actionsCritiques" 
                   onclick="mettreEnPaiementFicheFrais(); " title="Mettre en paiement la fiche de frais">
                    <img src="images/mettreEnPaiementIcon.png" class="icon" alt="icone Mettre en paiment" />
                </a> 
            </p>
        </form>

        <?php
    }
    ?>
</div>

<script type="text/javascript">   
<?php
require($repInclude . "_fonctionsValidFichesFrais.inc.js");
?>
</script>
<?php
require($repInclude . "_pied.inc.html");
require($repInclude . "_fin.inc.php");
?>