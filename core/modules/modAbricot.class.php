<?php
/* Collection of private function for ATM
 * Copyright (C) 2013-2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 	\defgroup	mymodule	MyModule module
 * 	\brief		MyModule module descriptor.
 * 	\file		core/modules/modMyModule.class.php
 * 	\ingroup	mymodule
 * 	\brief		Description and activation file for module MyModule
 */
include_once DOL_DOCUMENT_ROOT . "/core/modules/DolibarrModules.class.php";

/**
 * Description and activation class for module MyModule
 */
class modAbricot extends DolibarrModules
{

    /**
     * 	Constructor. Define names, constants, directories, boxes, permissions
     *
     * 	@param	DoliDB		$db	Database handler
     */
    public function __construct($db)
    {
        global $langs, $conf;

        $this->db = $db;

        // Id for module (must be unique).
        // Use a free id here
        // (See in Home -> System information -> Dolibarr for list of used modules id).
        $this->numero = 104999; // 104000 to 104999 for ATM CONSULTING
        // Key text used to identify module (for permissions, menus, etc...)
        $this->rights_class = 'abricot';

        // Family can be 'crm','financial','hr','projects','products','ecm','technic','other'
        // It is used to group modules in module setup page
        $this->family = "ATM Consulting";
        // Module label (no space allowed)
        // used if translation string 'ModuleXXXName' not found
        // (where XXX is value of numeric property 'numero' of module)
        $this->name = preg_replace('/^mod/i', '', get_class($this));
        // Module description
        // used if translation string 'ModuleXXXDesc' not found
        // (where XXX is value of numeric property 'numero' of module)
        $this->description = "Collection of specific ATM functions and classes";
        // Possible values for version are: 'development', 'experimental' or version
        $this->version = '3.9.6';
        $this->editor_name = 'ATM Consulting';
        $this->editor_url = 'https://www.atm-consulting.fr';
        // Key used in llx_const table to save module status enabled/disabled
        // (where MYMODULE is value of property name of module in uppercase)
        $this->const_name = 'MAIN_MODULE_' . strtoupper($this->name);
        // Where to store the module in setup page
        // (0=common,1=interface,2=others,3=very specific)
        $this->special = 2;
        // Name of image file used for this module.
        // If file is in theme/yourtheme/img directory under name object_pictovalue.png
        // use this->picto='pictovalue'
        // If file is in module/img directory under name object_pictovalue.png
        // use this->picto='pictovalue@module'
        $this->picto = 'coreatm.svg@abricot'; // mypicto@mymodule
        // Defined all module parts (triggers, login, substitutions, menus, css, etc...)
        // for default path (eg: /mymodule/core/xxxxx) (0=disable, 1=enable)
        // for specific path of parts (eg: /mymodule/core/modules/barcode)
        // for specific css file (eg: /mymodule/css/mymodule.css.php)
        $this->module_parts = array(
            // Set this to 1 if module has its own trigger directory
//            'triggers' => 1,
            // Set this to 1 if module has its own login method directory
            //'login' => 0,
            // Set this to 1 if module has its own substitution function file
            //'substitutions' => 0,
            // Set this to 1 if module has its own menus handler directory
            //'menus' => 0,
            // Set this to 1 if module has its own barcode directory
            //'barcode' => 0,
            // Set this to 1 if module has its own models directory
            //'models' => 0,
            // Set this to relative path of css if module has its own css file
            //'css' => '/mymodule/css/mycss.css.php',
            // Set here all hooks context managed by module
            //'hooks' => array('hookcontext1','hookcontext2')
            // Set here all workflow context managed by module
            //'workflow' => array('order' => array('WORKFLOW_ORDER_AUTOCREATE_INVOICE'))
        );

        // Data directories to create when module is enabled.
        // Example: this->dirs = array("/mymodule/temp");
        $this->dirs = array();

        // Config pages. Put here list of php pages
        // stored into mymodule/admin directory, used to setup module.
        $this->config_page_url = 'abricot_setup.php@abricot';

        // Dependencies
        // List of modules id that must be enabled if this module is enabled
        $this->depends = array();
        // List of modules id to disable if this one is disabled
        $this->requiredby = array();
        // Minimum version of PHP required by module
        $this->phpmin = array(7, 0);
        // Minimum version of Dolibarr required by module
        $this->need_dolibarr_version = array(16, 0);
        $this->langfiles = 'abricot@abricot'; // langfiles@mymodule
        // Constants
        // List of particular constants to add when module is enabled
        // (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
        // Example:
        $this->const = array(
            //	0=>array(
            //		'MYMODULE_MYNEWCONST1',
            //		'chaine',
            //		'myvalue',
            //		'This is a constant to add',
            //		1
            //	),
            //	1=>array(
            //		'MYMODULE_MYNEWCONST2',
            //		'chaine',
            //		'myvalue',
            //		'This is another constant to add',
            //		0
            //	)
        );

        // Array to add new pages in new tabs
        // Example:
        $this->tabs = array(
            //	// To add a new tab identified by code tabname1
            //	'objecttype:+tabname1:Title1:langfile@mymodule:$user->hasRight('mymodule', 'read'):/mymodule/mynewtab1.php?id=__ID__',
            //	// To add another new tab identified by code tabname2
            //	'objecttype:+tabname2:Title2:langfile@mymodule:$user->hasRight('othermodule', 'read'):/mymodule/mynewtab2.php?id=__ID__',
            //	// To remove an existing tab identified by code tabname
            //	'objecttype:-tabname'
        );
        // where objecttype can be
        // 'thirdparty'			to add a tab in third party view
        // 'intervention'		to add a tab in intervention view
        // 'order_supplier'		to add a tab in supplier order view
        // 'invoice_supplier'	to add a tab in supplier invoice view
        // 'invoice'			to add a tab in customer invoice view
        // 'order'				to add a tab in customer order view
        // 'product'			to add a tab in product view
        // 'stock'				to add a tab in stock view
        // 'propal'				to add a tab in propal view
        // 'member'				to add a tab in fundation member view
        // 'contract'			to add a tab in contract view
        // 'user'				to add a tab in user view
        // 'group'				to add a tab in group view
        // 'contact'			to add a tab in contact view
        // 'categories_x'		to add a tab in category view
        // (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
        // Dictionnaries
        if (! isModEnabled('mymodule')) {
            $conf->mymodule=new stdClass();
            $conf->mymodule->enabled = 0;
        }
        $this->dictionnaries = array();
        /* Example:
          // This is to avoid warnings
          if (! isModEnabled('mymodule')) $conf->mymodule->enabled=0;
          $this->dictionnaries=array(
          'langs'=>'mymodule@mymodule',
          // List of tables we want to see into dictonnary editor
          'tabname'=>array(
          MAIN_DB_PREFIX."table1",
          MAIN_DB_PREFIX."table2",
          MAIN_DB_PREFIX."table3"
          ),
          // Label of tables
          'tablib'=>array("Table1","Table2","Table3"),
          // Request to select fields
          'tabsql'=>array(
          'SELECT f.rowid as rowid, f.code, f.label, f.active'
          . ' FROM ' . MAIN_DB_PREFIX . 'table1 as f',
          'SELECT f.rowid as rowid, f.code, f.label, f.active'
          . ' FROM ' . MAIN_DB_PREFIX . 'table2 as f',
          'SELECT f.rowid as rowid, f.code, f.label, f.active'
          . ' FROM ' . MAIN_DB_PREFIX . 'table3 as f'
          ),
          // Sort order
          'tabsqlsort'=>array("label ASC","label ASC","label ASC"),
          // List of fields (result of select to show dictionnary)
          'tabfield'=>array("code,label","code,label","code,label"),
          // List of fields (list of fields to edit a record)
          'tabfieldvalue'=>array("code,label","code,label","code,label"),
          // List of fields (list of fields for insert)
          'tabfieldinsert'=>array("code,label","code,label","code,label"),
          // Name of columns with primary key (try to always name it 'rowid')
          'tabrowid'=>array("rowid","rowid","rowid"),
          // Condition to show each dictionnary
          'tabcond'=>array(
          isModEnabled('mymodule'),
          isModEnabled('mymodule'),
          isModEnabled('mymodule')
          )
          );
         */

        // Boxes
        // Add here list of php file(s) stored in core/boxes that contains class to show a box.
        $this->boxes = array(); // Boxes list
        $r = 0;
        // Example:

//        $this->boxes[$r][1] = "MyBox@mymodule";
//        $r ++;
        /*
          $this->boxes[$r][1] = "myboxb.php";
          $r++;
         */

        // Permissions
        $this->rights = array(); // Permission array used by this module
        $r = 0;

        // Add here list of permission defined by
        // an id, a label, a boolean and two constant strings.
        // Example:
        //// Permission id (must not be already used)
        //$this->rights[$r][0] = 2000;
        //// Permission label
        //$this->rights[$r][1] = 'Permision label';
        //// Permission by default for new user (0/1)
        //$this->rights[$r][3] = 1;
        //// In php code, permission will be checked by test
        //// if ($user->hasRight('permkey', 'level1', 'level2'))
        //$this->rights[$r][4] = 'level1';
        //// In php code, permission will be checked by test
        //// if ($user->hasRight('permkey', 'level1', 'level2'))
        //$this->rights[$r][5] = 'level2';
        //$r++;
        // Main menu entries
        $this->menus = array(); // List of menus to add
        $r = 0;

        // Add here entries to declare new menus
        //
        // Example to declare a new Top Menu entry and its Left menu entry:
        //$this->menu[$r]=array(
        //	// Put 0 if this is a top menu
        //	'fk_menu'=>0,
        //	// This is a Top menu entry
        //	'type'=>'top',
        //	'titre'=>'MyModule top menu',
        //	'mainmenu'=>'mymodule',
        //	'leftmenu'=>'mymodule',
        //	'url'=>'/mymodule/pagetop.php',
        //	// Lang file to use (without .lang) by module.
        //	// File must be in langs/code_CODE/ directory.
        //	'langs'=>'mylangfile',
        //	'position'=>100,
        //	// Define condition to show or hide menu entry.
        //	// Use 'isModEnabled('mymodule')' if entry must be visible if module is enabled.
        //	'enabled'=>'isModEnabled('mymodule')',
        //	// Use 'perms'=>'$user->hasRight('mymodule', 'level1', 'level2')'
        //	// if you want your menu with a permission rules
        //	'perms'=>'1',
        //	'target'=>'',
        //	// 0=Menu for internal users, 1=external users, 2=both
        //	'user'=>2
        //);
        //$r++;
        //$this->menu[$r]=array(
        //	// Use r=value where r is index key used for the parent menu entry
        //	// (higher parent must be a top menu entry)
        //	'fk_menu'=>'r=0',
        //	// This is a Left menu entry
        //	'type'=>'left',
        //	'titre'=>'MyModule left menu',
        //	'mainmenu'=>'mymodule',
        //	'leftmenu'=>'mymodule',
        //	'url'=>'/mymodule/pagelevel1.php',
        //	// Lang file to use (without .lang) by module.
        //	// File must be in langs/code_CODE/ directory.
        //	'langs'=>'mylangfile',
        //	'position'=>100,
        //	// Define condition to show or hide menu entry.
        //	// Use 'isModEnabled('mymodule')' if entry must be visible if module is enabled.
        //	'enabled'=>'isModEnabled('mymodule')',
        //	// Use 'perms'=>'$user->hasRight('mymodule', 'level1', 'level2')'
        //	// if you want your menu with a permission rules
        //	'perms'=>'1',
        //	'target'=>'',
        //	// 0=Menu for internal users, 1=external users, 2=both
        //	'user'=>2
        //);
        //$r++;
        //
        // Example to declare a Left Menu entry into an existing Top menu entry:
        //$this->menu[$r]=array(
        //	// Use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy'
        //	'fk_menu'=>'fk_mainmenu=mainmenucode',
        //	// This is a Left menu entry
        //	'type'=>'left',
        //	'titre'=>'MyModule left menu',
        //	'mainmenu'=>'mainmenucode',
        //	'leftmenu'=>'mymodule',
        //	'url'=>'/mymodule/pagelevel2.php',
        //	// Lang file to use (without .lang) by module.
        //	// File must be in langs/code_CODE/ directory.
        //	'langs'=>'mylangfile',
        //	'position'=>100,
        //	// Define condition to show or hide menu entry.
        //	// Use 'isModEnabled('mymodule')' if entry must be visible if module is enabled.
        //	// Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
        //	'enabled'=>'isModEnabled('mymodule')',
        //	// Use 'perms'=>'$user->hasRight('mymodule', 'level1', 'level2')'
        //	// if you want your menu with a permission rules
        //	'perms'=>'1',
        //	'target'=>'',
        //	// 0=Menu for internal users, 1=external users, 2=both
        //	'user'=>2
        //);
        //$r++;
        // Exports
        $r = 1;

        // Example:
        //$this->export_code[$r]=$this->rights_class.'_'.$r;
        //// Translation key (used only if key ExportDataset_xxx_z not found)
        //$this->export_label[$r]='CustomersInvoicesAndInvoiceLines';
        //// Condition to show export in list (ie: '$user->id==3').
        //// Set to 1 to always show when module is enabled.
        //$this->export_enabled[$r]='1';
        //$this->export_permission[$r]=array(array("facture","facture","export"));
        //$this->export_fields_array[$r]=array(
        //	's.rowid'=>"IdCompany",
        //	's.nom'=>'CompanyName',
        //	's.address'=>'Address',
        //	's.cp'=>'Zip',
        //	's.ville'=>'Town',
        //	's.fk_pays'=>'Country',
        //	's.tel'=>'Phone',
        //	's.siren'=>'ProfId1',
        //	's.siret'=>'ProfId2',
        //	's.ape'=>'ProfId3',
        //	's.idprof4'=>'ProfId4',
        //	's.code_compta'=>'CustomerAccountancyCode',
        //	's.code_compta_fournisseur'=>'SupplierAccountancyCode',
        //	'f.rowid'=>"InvoiceId",
        //	'f.facnumber'=>"InvoiceRef",
        //	'f.datec'=>"InvoiceDateCreation",
        //	'f.datef'=>"DateInvoice",
        //	'f.total'=>"TotalHT",
        //	'f.total_ttc'=>"TotalTTC",
        //	'f.tva'=>"TotalVAT",
        //	'f.paye'=>"InvoicePaid",
        //	'f.fk_statut'=>'InvoiceStatus',
        //	'f.note'=>"InvoiceNote",
        //	'fd.rowid'=>'LineId',
        //	'fd.description'=>"LineDescription",
        //	'fd.price'=>"LineUnitPrice",
        //	'fd.tva_tx'=>"LineVATRate",
        //	'fd.qty'=>"LineQty",
        //	'fd.total_ht'=>"LineTotalHT",
        //	'fd.total_tva'=>"LineTotalTVA",
        //	'fd.total_ttc'=>"LineTotalTTC",
        //	'fd.date_start'=>"DateStart",
        //	'fd.date_end'=>"DateEnd",
        //	'fd.fk_product'=>'ProductId',
        //	'p.ref'=>'ProductRef'
        //);
        //$this->export_entities_array[$r]=array('s.rowid'=>"company",
        //	's.nom'=>'company',
        //	's.address'=>'company',
        //	's.cp'=>'company',
        //	's.ville'=>'company',
        //	's.fk_pays'=>'company',
        //	's.tel'=>'company',
        //	's.siren'=>'company',
        //	's.siret'=>'company',
        //	's.ape'=>'company',
        //	's.idprof4'=>'company',
        //	's.code_compta'=>'company',
        //	's.code_compta_fournisseur'=>'company',
        //	'f.rowid'=>"invoice",
        //	'f.facnumber'=>"invoice",
        //	'f.datec'=>"invoice",
        //	'f.datef'=>"invoice",
        //	'f.total'=>"invoice",
        //	'f.total_ttc'=>"invoice",
        //	'f.tva'=>"invoice",
        //	'f.paye'=>"invoice",
        //	'f.fk_statut'=>'invoice',
        //	'f.note'=>"invoice",
        //	'fd.rowid'=>'invoice_line',
        //	'fd.description'=>"invoice_line",
        //	'fd.price'=>"invoice_line",
        //	'fd.total_ht'=>"invoice_line",
        //	'fd.total_tva'=>"invoice_line",
        //	'fd.total_ttc'=>"invoice_line",
        //	'fd.tva_tx'=>"invoice_line",
        //	'fd.qty'=>"invoice_line",
        //	'fd.date_start'=>"invoice_line",
        //	'fd.date_end'=>"invoice_line",
        //	'fd.fk_product'=>'product',
        //	'p.ref'=>'product'
        //);
        //$this->export_sql_start[$r] = 'SELECT DISTINCT ';
        //$this->export_sql_end[$r] = ' FROM (' . MAIN_DB_PREFIX . 'facture as f, '
        //	. MAIN_DB_PREFIX . 'facturedet as fd, ' . MAIN_DB_PREFIX . 'societe as s)';
        //$this->export_sql_end[$r] .= ' LEFT JOIN ' . MAIN_DB_PREFIX
        //	. 'product as p on (fd.fk_product = p.rowid)';
        //$this->export_sql_end[$r] .= ' WHERE f.fk_soc = s.rowid '
        //	. 'AND f.rowid = fd.fk_facture';
        //$r++;
    }

    /**
     * Function called when module is enabled.
     * The init function add constants, boxes, permissions and menus
     * (defined in constructor) into Dolibarr database.
     * It also creates data directories
     *
     * 	@param		string	$options	Options when enabling module ('', 'noboxes')
     * 	@return		int					1 if OK, 0 if KO
     */
    public function init($options = '')
    {
        $sql = array();

        $result = $this->loadTables();

        return $this->_init($sql, $options);
    }

    /**
     * Function called when module is disabled.
     * Remove from database constants, boxes and permissions from Dolibarr database.
     * Data directories are not deleted
     *
     * 	@param		string	$options	Options when enabling module ('', 'noboxes')
     * 	@return		int					1 if OK, 0 if KO
     */
    public function remove($options = '')
    {
        $sql = array();

        return $this->_remove($sql, $options);
    }

    /**
     * Create tables, keys and data required by module
     * Files llx_table1.sql, llx_table1.key.sql llx_data.sql with create table, create keys
     * and create data commands must be stored in directory /mymodule/sql/
     * This function is called by this->init
     *
     * 	@return		int		<=0 if KO, >0 if OK
     */
    private function loadTables()
    {
        return $this->_load_tables('/abricot/sql/');
    }
}
