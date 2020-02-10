<?php
/*
 Copyright (C) 2003-2013 Alexis Algoud <azriel68@gmail.com>
 Copyright (C) 2013-2015 ATM Consulting <support@atm-consulting.fr>

 This program and all files within this directory and sub directory
 is free software: you can redistribute it and/or modify it under 
 the terms of the GNU General Public License as published by the 
 Free Software Foundation, either version 3 of the License, or any 
 later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 
 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 
 This is a library for module setup page
 
 */

/**
 * Display title
 * @param string $title
 */
function setup_print_title($title="", $width = 300)
{
    global $langs;
    print '<tr class="liste_titre">';
    print '<th>'.$langs->trans($title).'</th>'."\n";
    print '<th align="center" width="20">&nbsp;</th>';
    print '<th align="center" width="'.$width.'"></th>'."\n";
    print '</tr>';
}


//
// _print_on_off('CONSTNAME', 'ParamLabel' , 'ParamDesc');

/**
 * yes / no select
 * @param string $confkey
 * @param string $title
 * @param string $desc
 *
 * exemple _print_on_off('CONSTNAME', 'ParamLabel' , 'ParamDesc');
 */
function setup_print_on_off($confkey, $title = false, $desc ='', $help = false, $width = 300)
{
    global $var, $bc, $langs, $conf, $form;
    $var=!$var;
    
    print '<tr '.$bc[$var].'>';
    print '<td>';
    if(!empty($help)){
        print $form->textwithtooltip( ($title?$title:$langs->trans($confkey)) , $langs->trans($help),2,1,img_help(1,''));
    }
    else {
        print $title?$title:$langs->trans($confkey);
    }
    
    if(!empty($desc))
    {
        print '<br><small>'.$langs->trans($desc).'</small>';
    }
    print '</td>';
    print '<td align="center" width="20">&nbsp;</td>';
    print '<td align="center" width="'.$width.'">';
    print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    print '<input type="hidden" name="action" value="set_'.$confkey.'">';
    print ajax_constantonoff($confkey);
    print '</form>';
    print '</td></tr>';
}


/**
 * Auto print form part for setup
 * @param string $confkey
 * @param string $title
 * @param string $desc
 * @param array $metas exemple use with color array('type'=>'color') or  with placeholder array('placeholder'=>'http://')
 * @param string $type = imput or textarea
 * @param string $help
 */
function setup_print_input_form_part($confkey, $title = false, $desc ='', $metas = array(), $type='input', $help = false, $width = 300)
{
    global $var, $bc, $langs, $conf, $db;
    $var=!$var;
    
    $form=new Form($db);
    
    $defaultMetas = array(
        'name' => $confkey
    );
    
    if($type!='textarea'){
        $defaultMetas['type']   = 'text';
        $defaultMetas['value']  = $conf->global->{$confkey};
    }
    
    
    $metas = array_merge ($defaultMetas, $metas);
    $metascompil = '';
    foreach ($metas as $key => $values)
    {
        $metascompil .= ' '.$key.'="'.$values.'" ';
    }
    
    print '<tr '.$bc[$var].'>';
    print '<td>';
    
    if(!empty($help)){
        print $form->textwithtooltip( ($title?$title:$langs->trans($confkey)) , $langs->trans($help),2,1,img_help(1,''));
    }
    else {
        print $title?$title:$langs->trans($confkey);
    }
    
    if(!empty($desc))
    {
        print '<br><small>'.$langs->trans($desc).'</small>';
    }
    
    print '</td>';
    print '<td align="center" width="20">&nbsp;</td>';
    print '<td align="right" width="'.$width.'">';
    print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'" '.($metas['type'] === 'file' ? 'enctype="multipart/form-data"' : '').'>';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    print '<input type="hidden" name="action" value="set_'.$confkey.'">';
    if($type=='textarea'){
        print '<textarea '.$metascompil.'  >'.dol_htmlentities($conf->global->{$confkey}).'</textarea>';
    }
    else {
        print '<input '.$metascompil.'  />';
    }
    
    print '<input type="submit" class="butAction" value="'.$langs->trans("Modify").'">';
    print '</form>';
    print '</td></tr>';
}