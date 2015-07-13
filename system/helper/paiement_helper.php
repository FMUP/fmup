<?php
/**
 * Classe PaiementHelper
 * @author : vbitaud
 * @description : Cette classe contient des m�thodes de manipulation de donn�es d�di�es � diff�rents moyens de paiement
 */
class PaiementHelper{

/********************************************************************************************
 ***************************** PAIEMENT VIA CM-CIC PAIEMENT *********************************
 *******************************************************************************************/
 
    /**
     * Retourne le code MAC n�cessaire pour une demande d'aurotisation de paiement
     * @param {params} : Un tableau contenant les param�tres permettant la construction du code MAC et contenant au moins les champs suivants
     *  - cle_hash : La cl� avec laquelle encoder la cl� MAC
     *  - code_tpe : Le num�ro du TPE utilis� pour le paiement (code � 7 caract�res)
     *  - date_commande : La date de la commande au format jj/mm/aaaa:hh:ii:ss
     *  - montant_commande : Le montant de la commande
     *  - code_devise : Le code sur 3 caract�res alphab�tiques ISO4217 de la devise utilis�e (EUR pour l'euro par d�faut)
     *  - reference_commande : La r�f�rence de la commande (sur 12 caract�res alphanum�riques maximum)
     *  - textelibre : Un texte libre (3200 caract�res maximum)
     *  - version : La version du module de paiement utilis� (3.0 par d�faut)
     *  - code_langue : Le code langue souhait� par l'utilisateur (on met anglais si aucun code n'est renseign�)
     *  - code_societe : Le code de la soci�t� fourni 
     *  - mail_client : L'adresse mail du client qui passe commande
     *  - nb_echeances : Le nombre d'�ch�ances de paiement (entre 2 et 4, uniquement pour le paiement fractionn�)
     *  - date_echeance1 : Date de la premi�re �ch�ance (uniquement pour le paiement fractionn�)
     *  - montant_echeance1 : Montant de la premi�re �ch�ance (uniquement pour le paiement fractionn�)
     *  - date_echeance2 : Date de la seconde �ch�ance (uniquement pour le paiement fractionn�)
     *  - montant_echeance2 : Montant de la seconde �ch�ance (uniquement pour le paiement fractionn�)
     *  - date_echeance3 : Date de la troisi�me �ch�ance (uniquement pour le paiement fractionn�)
     *  - montant_echeance3 : Montant de la trois�me �ch�ance (uniquement pour le paiement fractionn�)
     *  - date_echeance4 : Date de la quatri�me �ch�ance (uniquement pour le paiement fractionn�)
     *  - montant_echeance4 : Montant de la quatri�me �ch�ance (uniquement pour le paiement fractionn�)
     *  - type_paiement : D�finit quel mode de paiement a �t� utilis� (imm�diat ou diff�r�)
     * @param {options} : Les �ventuelles options pour la gestion du paiement
     * @return un tableau contenant le code MAC final si toutes les v�rifications sont bonnes et les �ventuelles erreurs rencontr�es
     */
    public static function getCodeMacDemandeAutorisationPaiement($params, $options = "")
    {
        $valeur_mac = "";
        $tab_erreurs = array();
        // V�rification du code TPE fourni
        if (!$params['code_tpe']) {
            $tab_erreurs['code_tpe'] = "Code TPE non renseign�";
        } elseif (!PaiementHelper::isCodeTpePaiementCic($params['code_tpe'])) {
            $tab_erreurs['code_tpe'] = "Code TPE non valide";
        }
        
        // V�rification du formattage des dates
        if (!self::isDateValidePaiementAvecHeure($params['date_commande'])) {
            $tab_erreurs['date_commande'] = "Format de date de commande non valide";
        }
        if ($params['date_echeance1'] && !self::isDateFrancaise($params['date_echeance1'])) {
            $tab_erreurs['date_echeance1'] = "Format de premi�re date d'�ch�ance non valide";
        }
        if ($params['date_echeance2'] && !self::isDateFrancaise($params['date_echeance2'])) {
            $tab_erreurs['date_echeance2'] = "Format de seconde date d'�ch�ance non valide";
        }
        if ($params['date_echeance3'] && !self::isDateFrancaise($params['date_echeance3'])) {
            $tab_erreurs['date_echeance3'] = "Format de troisi�me date d'�ch�ance non valide";
        }
        if ($params['date_echeance4'] && !self::isDateFrancaise($params['date_echeance4'])) {
            $tab_erreurs['date_echeance4'] = "Format de quatri�me date d'�ch�ance non valide";
        }
        // Formattage des montants
        if (!in_array($params['code_devise'], self::getTableauCodesMonnaiesValides())) {
            $tab_erreurs['code_devise'] = "Code devise non reconnu";
        } else {
            $montant = number_format($params['montant_total'], 2, '.', '').$params['code_devise'];
            if ($params['montant_echeance1']) {
                $params['montant_echeance1'] = number_format($params['montant_echeance1'], 2, '.', '').$params['code_devise'];
            }
            if ($params['montant_echeance2']) {
                $params['montant_echeance2'] = number_format($params['montant_echeance2'], 2, '.', '').$params['code_devise'];
            }
            if ($params['montant_echeance3']) {
                $params['montant_echeance3'] = number_format($params['montant_echeance3'], 2, '.', '').$params['code_devise'];
            }
            if ($params['montant_echeance4']) {
                $params['montant_echeance4'] = number_format($params['montant_echeance4'], 2, '.', '').$params['code_devise'];
            }
        }
        
        // V�rification du nombre d'�ch�ances entr�
        if ($params['nb_echeances']) {
            if (!Is::integer($params['nb_echeances'])) {
                $tab_erreurs['nb_echeances'] = "Le nombre d'�ch�ances entr� n'est pas un nombre entier";
            } else {
                if ($params['nb_echeances'] < 2 || $params['nb_echeances'] > 4) {
                    $tab_erreurs['nb_echeances'] = "Le nombre d'�ch�ances doit �tre compris entre 2 et 4 inclus";
                }
            }
        }
        
        // On met le code langue en anglais par d�faut si il n'est pas renseign�
        if (!$params['code_langue'] || !in_array($params['code_langue'], self::getTableauCodesLangueAcceptesPaiement())) {
            $params['code_langue'] = 'EN';
        }
        
        // Si pas d'erreur
        if (count($tab_erreurs) == 0) {
            // On construit la chine de caract�res
            $valeur_mac = $params['code_tpe'].
                            '*'.(($params['type_paiement'] == 'immediat') ? $params['date_commande'] : $params['date_paiement']).
                            '*'.$montant.
                            '*'.$params['reference_commande'].
                            '*'.$params['texte_libre'].
                            '*'.$params['version'].
                            '*'.$params['code_langue'].
                            '*'.$params['code_societe'].
                            '*'.$params['mail_client'].
                            '*'.$params['nb_echeances'].
                            '*'.$params['date_echeance1'].
                            '*'.$params['montant_echeance1'].
                            '*'.$params['date_echeance2'].
                            '*'.$params['montant_echeance2'].
                            '*'.$params['date_echeance3'].
                            '*'.$params['montant_echeance3'].
                            '*'.$params['date_echeance4'].
                            '*'.$params['montant_echeance4'].
                            '*'.$options;
            // Debug::output($valeur_mac, true);
            
            // Cryptage de la valeur MAC en utilisant SHA-1 et la cl� pass�e en param�tre
            $cle_utilisable = self::getCleUtilisable($params['cle_hash']);
            $valeur_mac = strtoupper(hash_hmac('sha1', $valeur_mac, $cle_utilisable));
        }
        return array('code_mac' => $valeur_mac, 'erreurs' => $tab_erreurs);
    }
    /**
     * Retourne le code MAC n�cessaire au recouvrement d'un paiement
     * @param {params} : Un tableau de param�tres permettant la construction de la cl� contenant au moins les champs suivants
     *  - cle_hash : La cl� avec laquelle encoder la cl� MAC
     *  - code_tpe : Le num�ro du TPE utilis� pour le paiement (code � 7 caract�res)
     *  - date_commande : La date de la commande au format jj/mm/aaaa:hh:ii:ss
     *  - montant_commande : Le montant total de la commande
     *  - montant_a_capturer : Le montant � capturer pour la commande
     *  - montant_deja_capture : Le montant total d�j� captur� pour la commande
     *  - montant_restant : Le montant restant � capturer pour la commande
     *  - code_devise : Le code sur 3 caract�res alphab�tiques ISO4217 de la devise utilis�e (EUR pour l'euro par d�faut)
     *  - reference_commande : La r�f�rence de la commande (sur 12 caract�res alphanum�riques maximum)
     *  - texte_libre : Un texte libre (3200 caract�res maximum)
     *  - version : La version du module de paiement utilis� (3.0 par d�faut)
     *  - code_langue : Le code langue souhait� par l'utilisateur (on met anglais si aucun code n'est renseign�)
     *  - code_societe : Le code de la soci�t� fourni
     * @return un tableau contenant le code MAC final si toutes les v�rifications sont bonnes et les �ventuelles erreurs rencontr�es
     */
    public static function getCodeMacRecouvrementPaiement($params)
    {
        $valeur_mac = "";
        $tab_erreurs = array();
        // V�rification du code TPE fourni
        if (!$params['code_tpe']) {
            $tab_erreurs['tpe'] = "Code TPE non renseign�";
        } elseif (!PaiementHelper::isCodeTpePaiementCic($params['code_tpe'])) {
            $tab_erreurs['tpe'] = "Code TPE non valide";
        }
        
        // V�rification du formattage des dates
        if (!self::isDateValidePaiementAvecHeure($params['date_commande'])) {
            $tab_erreurs['date_commande'] = "Format de date de commande non valide";
        }
        
        // Formattage des montants
        if (!in_array($params['code_devise'], self::getTableauCodesMonnaiesValides())) {
            $tab_erreurs['code_devise'] = "Code devise non reconnu";
        } else {
            // On v�rifie que la somme des trois montants est bien �gale au montant total de la commande
            $somme_montants = $params['montant_a_capturer'] + $params['montant_deja_capture'] + $params['montant_restant'];
            if ($somme_montants != $params['montant_total']) {
                $tab_erreurs['montant_commande'] = "La somme des montants n'est pas �gale au moment total de la commande.";
            } else {
                $params['montant_a_capturer'] = number_format($params['montant_a_capturer'], 2, '.', '').$params['code_devise'];
                $params['montant_deja_capture'] = number_format($params['montant_deja_capture'], 2, '.', '').$params['code_devise'];
                $params['montant_restant'] = number_format($params['montant_restant'], 2, '.', '').$params['code_devise'];
            }
        }
        
        // On met le code langue en anglais par d�faut si il n'est pas renseign�
        if (!$params['code_langue'] || !in_array($params['code_langue'], self::getTableauCodesLangueAcceptesPaiement())) {
            $params['code_langue'] = 'EN';
        }
        if (count($tab_erreurs) == 0) {
            // Cr�ation de la chaine de caract�re
            $valeur_mac = $params['code_tpe'].
                            '*'.$params['date_commande'].
                            '*'.$params['montant_a_capturer'].
                            $params['montant_deja_capture'].
                            $params['montant_restant'].
                            '*'.$params['reference_commande'].
                            '*'.$params['texte_libre'].
                            '*'.$params['version'].
                            '*'.$params['code_langue'].
                            '*'.$params['code_societe'].'*';
        
            // Cryptage de la valeur MAC en utilisant SHA-1 et la cl� pass�e en param�tre
            $cle_utilisable = self::getCleUtilisable($params['cle_hash']);
            $valeur_mac = strtoupper(hash_hmac('sha1', $valeur_mac, $params['cle_hash']));
        }
        return array('code_mac' => $valeur_mac, 'erreurs' => $tab_erreurs);
    }
    /**
     * Calcule le code MAC de r�ponse envoy� par la banque pour v�rification
     * @param {params} : Un tableau de param�tres permettant la construction de la cl� contenant au moins les champs suivants
     *  - cle_hash : La cl� avec laquelle encoder la cl� MAC
     *  - code_tpe : Le num�ro du TPE utilis� pour le paiement (code � 7 caract�res)
     *  - date_paiement : La date de la demande d'autorisation de paiement au format jj/mm/aaaa:hh:ii:ss
     *  - montant_total : Le montant total de la commande
     *  - code_devise : Le code sur 3 caract�res alphab�tiques ISO4217 de la devise utilis�e (EUR pour l'euro par d�faut)
     *  - reference_commande : La r�f�rence de la commande (sur 12 caract�res alphanum�riques maximum)
     *  - texte_libre : Un texte libre (3200 caract�res maximum)
     *  - version : La version du module de paiement utilis� (3.0 par d�faut)
     *  - code_retour : Le code retour envoy� par la banque
     *  - cryptogramme_saisi : Indique si le cryptogramme a bien �t� saisi
     *  - date_validite_carte : la date de validit� de la carte bancaire du client
     *  - type_carte : Le type de carte utilis� par le client (parmi AM, CB, MC, VI et na)
     *  - status3ds : Indicateur d'�change 3DSecure (parmi -1 (pas de 3DSecure), 1, 2, 3, 4)
     *  - numero_autorisation_banque : Num�ro d'autorisation fourni par la banque pour la transaction (uniquement renseign� si transaction  accept�e)
     *  - motif_refus : le motif de refus (uniquement renseign� si la demande de paiement a �t� refus�e)
     *  - origine_carte : Le code pays d'origine de la carte (uniquement renseign� si module pr�vention fraude)
     *  - code_banque_carte : Le code BIN de la banque client (uniquement renseign� si module pr�vention fraude)
     *  - hpan_cb : Hachage irr�versible du num�ro de carte client (uniquement renseign� si module pr�vention fraude)
     *  - ip_client : L'IP du client ayant fait la transaction (uniquement renseign� si module pr�vention fraude)
     *  - origine_transaction : Le code pays de l'origine de la transaction (uniquement renseign� si module pr�vention fraude)
     *  - etat_veres : Etat 3DSecure du VERes (uniquement renseign� si module pr�vention fraude et 3DSecure)
     *  - etat_pares : Etat 3DSecure du PARes (uniquement renseign� si module pr�vention fraude et 3DSecure)
     * @return un tableau contenant le code MAC final si toutes les v�rifications sont bonnes et les �ventuelles erreurs rencontr�es
     */
    public static function getCodeMacReponseBanque($params)
    {
        // On ne fait aucune v�rification car ce sont les champs renvoy�s directement par la banque
        $valeur_mac = $params['code_tpe'].
                        '*'.$params['date_paiement'].
                        '*'.$params['montant_total'].
                        '*'.$params['reference_commande'].
                        '*'.$params['texte_libre'].
                        '*'.$params['version'].
                        '*'.$params['code_retour'].
                        '*'.$params['cryptogramme_saisi'].
                        '*'.$params['date_validite_carte'].
                        '*'.$params['type_carte'].
                        '*'.$params['status3ds'].
                        '*'.$params['numero_autorisation_banque'].
                        '*'.$params['motif_refus'].
                        '*'.$params['origine_carte'].
                        '*'.$params['code_banque_carte'].
                        '*'.$params['hpan_cb'].
                        '*'.$params['ip_client'].
                        '*'.$params['origine_transaction'].
                        '*'.$params['etat_veres'].
                        '*'.$params['etat_pares'].'*';
        
        // Cryptage de la valeur MAC en utilisant SHA-1 et la cl� pass�e en param�tre
        $cle_utilisable = self::getCleUtilisable($params['cle_hash']);
        $valeur_mac = strtoupper(hash_hmac('sha1', $valeur_mac, $cle_utilisable));
        return $valeur_mac;
    }
    /**
     * Renvoie une version utilisable pour l'algorithme HMAC pour le calcul du code MAC
     * @param {cle_hash} : La cl� � rendre utilisable
     */
    public static function getCleUtilisable($cle_hash)
    {
        $hexStrKey  = substr($cle_hash, 0, 38);
        $hexFinal   = "" . substr($cle_hash, 38, 2) . "00";
    
        $cca0=ord($hexFinal); 

        if ($cca0>70 && $cca0<97) 
            $hexStrKey .= chr($cca0-23) . substr($hexFinal, 1, 1);
        else { 
            if (substr($hexFinal, 1, 1)=="M") 
                $hexStrKey .= substr($hexFinal, 0, 1) . "0"; 
            else 
                $hexStrKey .= substr($hexFinal, 0, 2);
        }

        return pack("H*", $hexStrKey);
    }

     /**
     * Renvoie la liste des codes langue valides pour le formulaire de paiement
     */
    public static function getTableauCodesLangueAcceptesPaiement()
    {
        return array(
                        1 => 'DE',
                        2 => 'EN',
                        3 => 'ES',
                        4 => 'FR',
                        5 => 'IT',
                        6 => 'JA',
                        7 => 'NL',
                        8 => 'PT',
                        9 => 'SV');
    }
    /**
     * Renvoie la liste des codes de monnaie ISO4217 accept�s pour le paiement
     */
    public static function getTableauCodesMonnaiesValides()
    {
        return array('AED', 'AFN', 'ALL', 'AMD', 'ANG', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN', 'BAM', 'BBD', 'BDT', 'BGN',
                        'BHD', 'BIF', 'BMD', 'BND', 'BOB', 'BOV', 'BRL', 'BSD', 'BTN', 'BWP', 'BYR', 'BZD', 'CAD', 'CDF', 'CHE', 'CHF',
                        'CHW', 'CLF', 'CLP', 'CNY', 'COP', 'COU', 'CRC', 'CUC', 'CUP', 'CVE', 'CZK', 'DJF', 'DKK', 'DOP', 'DZD', 'EGP',
                        'ERN', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GHS', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK',
                        'HTG', 'HUF', 'IDR', 'ILS', 'INR', 'IQD', 'IRR', 'ISK', 'JMD', 'JOD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KPW',
                        'KRW', 'KWD', 'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'LTL', 'LYD', 'MAD', 'MDL', 'MGA', 'MKD', 'MMK',
                        'MNT', 'MOP', 'MRO', 'MUR', 'MVR', 'MWK', 'MXN', 'MXV', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD',
                        'OMR', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR',
                        'SDG', 'SEK', 'SGD', 'SHP', 'SLL', 'SOS', 'SRD', 'SSP', 'STD', 'SVC', 'SYP', 'SZL', 'THB', 'TJS', 'TMT', 'TND',
                        'TOP', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH', 'UGX', 'USD', 'USN', 'UYI', 'UYU', 'UZS', 'VEF', 'VND', 'VUV', 'WST',
                        'XAF', 'XCD', 'XDR', 'XOF', 'XPF', 'XSU', 'XUA', 'YER', 'ZAR', 'ZAR', 'ZMW', 'ZWL'
                        );
    }
    
    /**
     * Teste si la chaine pass�e est un code TPE valide pour le paiement via l'application CIC Paiement
     * NB : Un code TPE valide est un code de 7 caract�res alphanum�riques
     * @param {chaine} : La cha�ne � tester
     */
    public static function isCodeTpePaiementCic($chaine)
    {
        if (!Is::chaineOuNombre($chaine)) {
            return false;
        }
        return (bool)preg_match('#^[A-Z,0-9,a-z]{7}$#', $chaine);
    }
    /**
     * Teste si une date est valide pour la cr�ation d'une chaine MAC
     * NB : Les dates valides sont au format jj/mm/aaaa:hh:ii:ss uniquement
     * @param {date} : La date � tester
     */
    public static function isDateValidePaiementAvecHeure($date)
    {
        if (!Is::chaineOuNombre($date)) {
            return false;
        }
        return (bool)preg_match('#^([0-3][0-9])/([0-1][0-9])/(20[0-9][0-9]):([0-2][0-9]):([0-5][0-9]):([0-9][0-9])$#', $date);
    }
    /**
     * Valide que la date donn�e au format fran�ais (SANS heure) existe bien
     * @param mixed $valeur : La variable test�e
     * @return bool : VRAI si la valeur pass�e en param�tre une date au format JJ/MM/AAAA ou JJ/MM/AA, avec comme s�parateur / uniquement
     */
    public static function isDateFrancaise($valeur)
    {
        if (is_string($valeur)) {
            $resultat = preg_split('|/|', $valeur);
            if (count($resultat) == 3) {
                list($jour, $mois, $annee) = $resultat;
                if (Is::integer($jour) && Is::integer($mois) && Is::integer($annee)) {
                    if (strlen($annee) == 2) $annee = '20'.$annee;
                    if ($annee < 1000) return false; 
                    if ($annee > 9999) return false; 
                    return checkDate($mois, $jour, $annee);
                }
            }
        }
        return false;
    }
}
