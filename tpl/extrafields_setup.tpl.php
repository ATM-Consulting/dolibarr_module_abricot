<?php

// EXEMPLE OF USAGE IN YOUR admin/mymodule_extrafields.php
///*
// * Config of extrafield page for MyModule
// */
//require_once '../lib/mymodule.lib.php';
//$langs->loadLangs(array("mymodule@mymodule", "admin", "other"));
//
//$mymodule = new MyModule($db);
//$elementtype=$mymodule->table_element;  //Must be the $table_element of the class that manage extrafield
//
//// Page title and texts elements
//$textobject=$langs->transnoentitiesnoconv("MyModule");
//$help_url='EN:Help MyModule|FR:Aide MyModule';
//$pageTitle = $langs->trans("MyModuleExtrafieldPage");
//
//// Configuration header
//$head = mymoduleAdminPrepareHead();
//
//
//
///*
// *  Include of extrafield page
// */
//
//define('loadedFormModuleExtrafieldPage', true);
//require_once dol_buildpath('abricot/tpl/extrafields_setup.tpl.php'); // use this kind of call for variables scope





require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';


$extrafields = new ExtraFields($db);
$form = new Form($db);

// List of supported format
$tmptype2label=ExtraFields::$type2label;
$type2label=array('');
foreach ($tmptype2label as $key => $val) $type2label[$key]=$langs->transnoentitiesnoconv($val);

$action=GETPOST('action', 'alpha');
$attrname=GETPOST('attrname', 'alpha');

if (!$user->admin) accessforbidden();


/*
* Actions
*/

require DOL_DOCUMENT_ROOT.'/core/actions_extrafields.inc.php';



/*
* View
*/

llxHeader('', $pageTitle, $help_url);


$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1">'.$langs->trans("BackToModuleList").'</a>';
print load_fiche_titre($langs->trans($pageTitle),$linkback,'title_setup');



$activeTab = !empty($activeTab)?$activeTab:'extrafields';

if (empty($picto)) $picto='generic';
dol_fiche_head($head, $activeTab, $textobject, -1, $picto);

require DOL_DOCUMENT_ROOT.'/core/tpl/admin_extrafields_view.tpl.php';

dol_fiche_end();


// Buttons
if ($action != 'create' && $action != 'edit')
{
print '<div class="tabsAction">';
    print "<a class=\"butAction\" href=\"".$_SERVER["PHP_SELF"]."?action=create#newattrib\">".$langs->trans("NewAttribute")."</a>";
    print "</div>";
}


/* ************************************************************************** */
/*                                                                            */
/* Creation d'un champ optionnel
/*                                                                            */
/* ************************************************************************** */

if ($action == 'create')
{
print '<br><div id="newattrib"></div>';
print load_fiche_titre($langs->trans('NewAttribute'));

require DOL_DOCUMENT_ROOT.'/core/tpl/admin_extrafields_add.tpl.php';
}

/* ************************************************************************** */
/*                                                                            */
/* Edition d'un champ optionnel                                               */
/*                                                                            */
/* ************************************************************************** */
if ($action == 'edit' && ! empty($attrname))
{
print '<br><div id="editattrib"></div>';
print load_fiche_titre($langs->trans("FieldEdition", $attrname));

require DOL_DOCUMENT_ROOT.'/core/tpl/admin_extrafields_edit.tpl.php';
}

// End of page
llxFooter();
$db->close();
