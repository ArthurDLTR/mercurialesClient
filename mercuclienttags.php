<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *	\file       mercurialesclient/mercurialesclientindex.php
 *	\ingroup    mercurialesclient
 *	\brief      Home page of mercurialesclient top menu
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formadmin.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/comm/propal/class/propal.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formsetup.class.php';
if (isModEnabled('adherent')) {
	require_once DOL_DOCUMENT_ROOT.'/adherents/class/adherent.class.php';
}
if (isModEnabled('accounting')) {
	require_once DOL_DOCUMENT_ROOT.'/core/lib/accounting.lib.php';
}
if (isModEnabled('accounting')) {
	require_once DOL_DOCUMENT_ROOT.'/core/class/html.formaccounting.class.php';
}
if (isModEnabled('accounting')) {
	require_once DOL_DOCUMENT_ROOT.'/accountancy/class/accountingaccount.class.php';
}
if (isModEnabled('eventorganization')) {
	require_once DOL_DOCUMENT_ROOT.'/eventorganization/class/conferenceorboothattendee.class.php';
}

// Load translation files required by the page
$langs->loadLangs(array("mercurialesclient@mercurialesclient"));

$langs->loadLangs(array("companies", "commercial", "bills", "banks", "users"));

if (isModEnabled('adherent')) {
	$langs->load("members");
}
if (isModEnabled('categorie')) {
	$langs->load("categories");
}
if (isModEnabled('incoterm')) {
	$langs->load("incoterm");
}
if (isModEnabled('notification')) {
	$langs->load("mails");
}
if (isModEnabled('accounting')) {
	$langs->load("products");
}

$error = 0; $errors = array();

$action = GETPOST('action', 'aZ09');

// Security check - Protection if external user
$socid = GETPOST('socid', 'int') ? GETPOST('socid', 'int') : GETPOST('id', 'int');
if (isset($user->socid) && $user->socid > 0) {
	$action = '';
	$socid = $user->socid;
}


// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//if (!isModEnabled('mercurialesclient')) {
	//	accessforbidden('Module not enabled');
	//}
	//if (! $user->hasRight('mercurialesclient', 'myobject', 'read')) {
		//	accessforbidden();
		//}
		//restrictedArea($user, 'mercurialesclient', 0, 'mercurialesclient_myobject', 'myobject', '', 'rowid');
		//if (empty($user->admin)) {
			//	accessforbidden('Must be admin');
//}


/*
* Actions
*/
// Get the max date for date of creation of the object
if (GETPOST('start_date', 'alpha')){
	$start_date = GETPOST('start_date', 'alpha');
} else {
	$start_date = '';
}

// Get the customer tags selected
if(GETPOST('thirdparty_tag', 'alpha')){
	$soc_tag = GETPOST('thirdparty_tag', 'alpha');
	$soc_tags = '(';
	foreach ($soc_tag as $tag){
		$soc_tags.= $tag.',';
	}
	$soc_tag = substr($soc_tags, 0, -1).')';
} else {
	$soc_tag = '';
}

// Get the product tags selected
if(GETPOST('product_tag', 'alpha')){
	$prod_tag = GETPOST('product_tag', 'alpha');
	$prod_tags = '(';
	foreach ($prod_tag as $tag){
		$prod_tags.= $tag.',';
	}
	$prod_tag = substr($prod_tags, 0, -1).')';
} else {
	$prod_tag = '';
}

// Get the id of the thirdparty selected
if (GETPOST('socidSelected', 'alpha')){
	$socidSelected = GETPOST('socidSelected', 'alpha');
} else {
	$socidSelected = '';
}

// Get the customer ref typed
if(GETPOST('customer_ref', 'alpha')){
	$customer_ref = GETPOST('customer_ref', 'alpha');
} else {
	$customer_ref = '';
}

// Get the type of document
if (GETPOST('doc_type', 'alpha')){
	$doctype = GETPOST('doc_type', 'alpha');
}

/*
* View
*/

$form = new Form($db);
$formSetup = new FormSetup($db);
$object = new Societe($db);
$product = new Product($db);
$propal = new Propal($db);
$commande = new Commande($db);

// Variables to define the limits of the request and the number of rows printed
$limit = GETPOSTINT('limit') ? GETPOSTINT('limit') : $conf->liste_limit;
$page = GETPOSTINT('pageplusone') ? (GETPOSTINT('pageplusone') - 1) : GETPOSTINT("page");
if(empty($page) || $page < 0){
	// If $page is not defined, or '' or -1 or if we click on clear filters
	$page = 0;
}
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

if ($offset > $totalnumofrows) {	// if total resultset is smaller than the paging size (filtering), goto and load page 0
	$page = 0;
	$offset = 0;
}


$object->fetch($socid);
// print date('Y-m-j', dol_now());


if ($user->hasRight('mercurialesclient', 'mercu_object', 'read')){
	if($doctype == 'commande'){
		// SQL request if we use orders
		$sql = 'SELECT cd.fk_commande as commande_id, cd.fk_product as prod_id, cd.qty as qty, cd.subprice as price, cd.remise_percent as remise, c.fk_soc as societe';
		$sql.= ' FROM '.MAIN_DB_PREFIX.'commande as c';
		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'commandedet as cd on cd.fk_commande = c.rowid';
		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'product as prod on prod.rowid = cd.fk_product';
		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'categorie_societe as cs on cs.fk_soc = c.fk_soc';
        $sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'categorie_product as cp on cp.fk_product = cd.fk_product';
		
		// Remove the product if not to buy and to sell
		$sql.= ' WHERE prod.tosell = 1 AND prod.tobuy = 1';
        // Select a specific thirdparty
		if ($socidSelected && $socidSelected != -1){
			$sql.= ' AND c.fk_soc = '.$socidSelected;
		}
		// If start_date exists, we only get the products in proposals after this date
		if ($start_date){
			$sql.= " AND c.date_valid >= '".$start_date."'";
		}
        // If a thirdparty categorie is selected, we limit the orders
        if ($soc_tag){
            $sql.= " AND cs.fk_categorie IN ".$soc_tag;
        }
        // If a product tag is selected, we limit the orders
        if($prod_tag){
            $sql.= " AND cp.fk_categorie IN ".$prod_tag;
        }
		$sql.= ' AND c.date_valid = (SELECT MAX(cr.date_valid) FROM llx_commande as cr 
            LEFT JOIN '.MAIN_DB_PREFIX.'commandedet as crd on crd.fk_commande = cr.rowid 
            LEFT JOIN '.MAIN_DB_PREFIX.'categorie_societe as crs on crs.fk_soc = cr.fk_soc
            LEFT JOIN '.MAIN_DB_PREFIX.'categorie_product as crp on crp.fk_product = crd.fk_product WHERE cd.fk_product = crd.fk_product';
		// Select a specific thirdparty
		if ($socidSelected && $socidSelected != -1){
			$sql.= ' AND cr.fk_soc = '.$socidSelected;
		}
		// Only get the order with the correct Customer Ref
		if ($customer_ref){
			$sql.= " AND cr.ref_client LIKE '%".$customer_ref."%'";
		}
		// If a thirdparty categorie is selected, we limit the orders
        if ($soc_tag){
            $sql.= " AND crs.fk_categorie IN ".$soc_tag;
        }
        // If a product tag is selected, we limit the orders
        if($prod_tag){
            $sql.= " AND crp.fk_categorie IN ".$prod_tag;
        }
		$sql.= ')';
		$sql.= ' GROUP BY cd.fk_product ORDER BY cd.fk_commande DESC';
	} else if ($doctype=="propal") {
		// SQL request if we use orders
		$sql = 'SELECT pd.fk_propal as propal_id, pd.fk_product as prod_id, pd.qty as qty, pd.subprice as price, pd.remise_percent as remise, p.fk_soc as societe';
		$sql.= ' FROM '.MAIN_DB_PREFIX.'propal as p';
		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'propaldet as pd on pd.fk_propal = p.rowid';
		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'product as prod on prod.rowid = pd.fk_product';
		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'categorie_societe as cs on cs.fk_soc = p.fk_soc';
        $sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'categorie_product as cp on cp.fk_product = pd.fk_product';
        // Remove the product if not to buy and to sell
		$sql.= ' WHERE prod.tosell = 1 AND prod.tobuy = 1';
		// Select a specific thirdparty
		if ($socidSelected && $socidSelected != -1){
			$sql.= ' AND p.fk_soc = '.$socidSelected;
		}
		// If start_date exists, we only get the products in proposals after this date
		if ($start_date){
			$sql.= " AND p.date_valid >= '".$start_date."'";
		}
        // If a thirdparty categorie is selected, we limit the orders
        if ($soc_tag){
            $sql.= " AND cs.fk_categorie IN ".$soc_tag;
        }
        // If a product tag is selected, we limit the orders
        if($prod_tag){
            $sql.= " AND cp.fk_categorie IN ".$prod_tag;
        }
		// Only get the product on the last proposal it appears
		$sql.= ' AND p.date_valid = (SELECT MAX(pr.date_valid) FROM llx_propal as pr 
            LEFT JOIN '.MAIN_DB_PREFIX.'propaldet as prd on prd.fk_propal = pr.rowid 
            LEFT JOIN '.MAIN_DB_PREFIX.'categorie_societe as crs on crs.fk_soc = pr.fk_soc
            LEFT JOIN '.MAIN_DB_PREFIX.'categorie_product as crp on crp.fk_product = prd.fk_product WHERE pd.fk_product = prd.fk_product';
		// Select a specific thirdparty
		if ($socidSelected && $socidSelected != -1){
			$sql.= ' AND pr.fk_soc = '.$socidSelected;
		}
		// Only get the proposal ith the correct Customer Ref
		if ($customer_ref){
			$sql.= " AND pr.ref_client LIKE '%".$customer_ref."%'";
		}
		// If a thirdparty categorie is selected, we limit the orders
        if ($soc_tag){
            $sql.= " AND crs.fk_categorie IN ".$soc_tag;
        }
        // If a product tag is selected, we limit the orders
        if($prod_tag){
            $sql.= " AND crp.fk_categorie IN ".$prod_tag;
        }
		$sql.= ')';
		$sql.= ' GROUP BY pd.fk_product ORDER BY pd.fk_propal DESC';
	} else if ($doctype == "facture"){

	}
	// print $sql;
	$resql = $db->query($sql);	
	
	// Check if the button was clicked
	if ($action == 'create_mercu' && GETPOSTISSET('createBtn', 'bool')){
		// Create proposal with all the products from previous proposals
		$prop = new Propal($db, $socid);
		$prop->date_creation = dol_now();
		$prop->date = dol_now();
		
		$soc = new Societe($db);
		$soc->fetch($socid);
		$prop->thirdparty = $soc;
		
		
		$num = $db->num_rows($resql);
		// Loop on each product to had them to the proposal
		for($i = 0; $i < $num; $i++){
			$obj = $db->fetch_object($resql);
			$product->fetch($obj->prod_id);
			$resAdd = $prop->add_product($obj->prod_id, 1, $obj->remise);
			// $resAdd = $prop->addline('', $obj->price, 1, $product->tva_tx, $product->localtax1_tx, $product->localtax2_tx, $obj->prod_id, $obj->remise, 'HT', 0, 0, -1);
			// print "Ajout réussi : ".$resAdd;
		}
		// print 'erreur : ';
		// var_dump($prop->error);
		// print '<br>erreurs : ';
		// var_dump($prop->errors);
		$resCreation = $prop->create($user);
		
		// print 'ID propale : '.$prop->id.' et sa ref '.$prop->ref;
		if ($resCreation >= 0){
			if (getDolGlobalInt('MERCURIALESCLIENT_OPENPROPOSAL')){
				header("Location: ".DOL_URL_ROOT.'/comm/propal/card.php?id='.$prop->id);
			} else {
				$text = $langs->trans("MERCU_SUCCESS_CREATE").' <a href="'.DOL_URL_ROOT.'/comm/propal/card.php?id='.$prop->id.'">'.$prop->ref.'</a>';
				setEventMessages($text, null, 'mesgs');
			}
		} else {
			setEventMessages("FAILED ", null, 'errors');
		}
		$resql = $db->query($sql);
	}
	
	// Content of the page
	llxHeader("", $object->name.' - '.$langs->trans("Mercuriale"), '', '', 0, 0, '', '', '', 'mod-mercurialesclient page-index');
	
	// Thirdparty banner
	$title = $langs->trans("ThirdParty");	
	
	$head = societe_prepare_head($object);
	// print "Object id : ".$object->id;
	
	print dol_get_fiche_head($head, 'mercuclienttag', $langs->trans("ThirdParty"), -1, 'company');
	
	dol_banner_tab($object, 'socid', '', ($user->socid ? 0 : 1), 'rowid', 'nom');
	
	// List of the products in all the proposals of the customer
	print '<div class="fichecenter">';
	
	
	if($limit){
		$sql.=$db->plimit($limit + 1, $offset);
	}
	
	$num = $db->num_rows($resql);
	
	$imax = ($limit ? min($num, $limit) : $num);
	// $button = '<form method="POST" id="createPropal" action="'.$_SERVER["PHP_SELF"].'?socid='.$socid.'&action=create_mercu">';
	// $button = '<input type="hidden" name="token" value="'.newToken().'">';
	$button = '<input type="submit" class="button buttonform" name="createBtn" value="'.$langs->trans("MERCU_BUTTON_TEXT").'">';
	$button.= '</form>';
	print '<form method="POST" id="createPropal" action="'.$_SERVER["PHP_SELF"].'?socid='.$socid.'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print_barre_liste($langs->trans("Products"), $page, $_SERVER["PHP_SELF"], '', '', '', '', $num, $num, 'product', 0, '', '', $limit, 0, 0, 1);
	print '</form>';

	// Form for the date
	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'?socid='.$socid.'&action=create_mercu">';
	print '<input type="hidden" name="token" value="'.newToken().'">';

	// Type of document ot use
	print '<label for="doc_type">'.$langs->trans('DocumentType').'</label>';
	print '<select id="doc_type" name="doc_type">';
	print '<option value="propal" '.($doctype == 'propal' ? 'selected' : '').' >'.$langs->trans('Proposal').'</option>';
	print '<option value="commande" '.($doctype == 'commande' ? 'selected' : '').' >'.$langs->trans('Order').'</option>';
	print '<option value="facture" '.($doctype == 'facture' ? 'selected' : '').' >'.$langs->trans('Invoice').'</option>';
	
	print '</select>';
	print '<br>';
	
	// Date choice
	print '<label for="starting_date">' . $langs->trans('START_DATE') . '</label>';
	print '<input type="date" id="start_date" name="start_date" value="'.$start_date.'">';
	print '<br>';
	
	// Product tag selection
	print '<label for="product_tag">'.$langs->trans('ProductTag').'</label>';
    $cate = $form->select_all_categories(Categorie::TYPE_PRODUCT, '', 'parent', 64, 0, 1);
    // var_dump($cate);
    // print '<select id="product_tag" name="product_tag"><option value=""></option>';
    // // Adding each product tag to the select options
    // foreach($cate as $key => $value){
    //     if ($prod_tag == $key){
    //         print '<option selected value="'.$key.'">'.$value.'</option>';
    //     } else {
    //         print '<option value="'.$key.'">'.$value.'</option>';
    //     }
    // }

    // print '</select><br>';
	print $form->multiselectarray('product_tag', $cate, GETPOST('product_tag', 'alpha'));
	print '<br>';

    // Thirdparty tag selection
    print '<label for="thirdparty_tag">'.$langs->trans('ThirdpartyTag').'</label>';
    $cate = $form->select_all_categories(Categorie::TYPE_CUSTOMER, '', 'parent', 64, 0, 1);
    // print '<select id="thirdparty_tag" name="thirdparty_tag">';
    // print '<option value=""></option>';
    // // Adding each thirdparty tag to the select options
    // foreach($cate as $key => $value){
    //     if ($soc_tag == $key){
    //         print '<option selected value="'.$key.'">'.$value.'</option>';
    //     } else {
    //         print '<option value="'.$key.'">'.$value.'</option>';
    //     }
    // }
    // print '</select>';
	print $form->multiselectarray('thirdparty_tag', $cate, GETPOST('thirdparty_tag', 'alpha'));
	print '<br>';
	
	// Box to choose a thirdparty to copy the price list
	print $langs->trans('ThirdParty').' '.img_picto('', 'company', 'class="pictofixedwidth"').$form->select_company($socidSelected, 'socidSelected', '((s.client:IN:1,2,3) AND (s.status:=:1))', 'SelectThirdParty', 1, 0, null, 0, 'minwidth175 maxwidth300 widthcentpercentminusxx');
	print '<br>';

	// Text box for search on Customer ref
	print '<label for="customer_ref">'.$langs->trans('RefCustomer').'</label>';
	print '<input type"text" id="customer_ref" name="customer_ref" value="'.$customer_ref.'">';
	
	// Update button 
	print '<input type="submit" class="button buttonform small" value="'.$langs->trans("UPDATE").'">';
	print '<br>';

	print $button;

	print '<br>';

	print '<table class="noborder centpercent">';
	print '<tr class="liste_titre">';
	print '<th>'.$langs->trans('Product').($imax?'<span class="badge marginleftonlyshort">'.$imax.'</span>':'').'</th>';
	if($doctype == 'commande'){
		print '<th>'.$langs->trans('Order').($imax?'<span class="badge marginleftonlyshort">'.$imax.'</span>':'').'</th>';
	} else if ($doctype == 'propal'){
		print '<th>'.$langs->trans('Proposal').($imax?'<span class="badge marginleftonlyshort">'.$imax.'</span>':'').'</th>';
	} else if ($doctype == 'facture'){
		print '<th>'.$langs->trans('Invoice').($imax?'<span class="badge marginleftonlyshort">'.$imax.'</span>':'').'</th>';
	}
    print '<th>'.$langs->trans('ThirdParty').($imax?'<span class="badge marginleftonlyshort">'.$imax.'</span>':'').'</th>';
	print '<th>'.$langs->trans('Price').($imax?'<span class="badge marginleftonlyshort">'.$imax.'</span>':'').'</th>';
	print '<th>'.$langs->trans("Quantity").($imax?'<span class="badge marginleftonlyshort">'.$imax.'</span>':'').'</th>';
	print '</tr>';

    $soc = new Societe($db);

	$i = 0;
	while ($i < $imax){
		$obj = $db->fetch_object($resql);
		$product->fetch($obj->prod_id);
		if($doctype == 'commande'){
			$commande->fetch($obj->commande_id);
		} else if ($doctype == 'propal'){
			$propal->fetch($obj->propal_id);
		} else if ($doctype == 'facture'){
			$fac->fetch($obj->fac_id);
		}
        $soc->fetch($obj->societe);

		print '<tr class="oddeven">';
		print '<td class="nowrap">'.$product->getNomUrl(1).'</td>';
		if($doctype == 'commande'){
			print '<td class="nowrap">'.$commande->getNomUrl(1).'</td>';
		} else if ($doctype == 'propal'){
			print '<td class="nowrap">'.$propal->getNomUrl(1).'</td>';
		} else if ($doctype == 'facture'){
			print '<td class="nowrap">'.$fac->getNomUrl(1).'</td>';
		}
        print '<td class="nowrap">'.$soc->getNomUrl(1).'</td>';
		print '<td class="nowrap">'.$obj->price * ((100 - $obj->remise) / 100) .'</td>';
		print '<td class="nowrap">'.$obj->qty.'</td>';

		$i++;
	}
	

}

print '</div><div class="fichetwothirdright">';


$NBMAX = getDolGlobalInt('MAIN_SIZE_SHORTLIST_LIMIT');
$max = getDolGlobalInt('MAIN_SIZE_SHORTLIST_LIMIT');

print '</div></div>';

// End of page
llxFooter();
$db->close();
