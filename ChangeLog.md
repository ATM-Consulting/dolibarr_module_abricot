# Abricot - Changelog
All notable changes to this project will be documented in this file.
___

# NOT RELEASED




## RELEASE 3.5
- FIX : setup_print_title function correction *03/08/2023* - 3.5.7
- FIX : Query escape column names and Mysql/Pgsql compatibility *24/03/2023* - 3.5.6
- FIX : Substitution script include ticket ref *3.5.5* - **27/02/2023**
- FIX : script de migration des ticketsup prend en compte la ref dans actioncomm  + traitement erreur extrafields - *21/12/2022* - 3.5.4  
- FIX : Retro compat *18/11/2022* - 3.5.3
- FIX : Fix divers warnings PHP8 *16/08/2022* - 3.5.2
- FIX : Change editor name : ATM-Consulting -> ATM Consulting *3.5.1* - **09/08/2022**
- NEW : Ajout d'un filtre sur listview permettant au multiselect de trier avec plusieurs tags *27/07/2022* - 3.5.0


## RELEASE 3.4 - 11/03/2022
- FIX : Fatal inclusion lib fail - *09/08/2022* - 3.4.10
- FIX : V16 FAMILY  - *02/06/2022* - 3.4.9
- FIX : V16 NewToken() - *02/06/2022* - 3.4.8
- FIX : correction récupération TVA dans script migration des ndfp vers notes de frais std *17/05/2022* - 3.4.7
- FIX : Old Icon *21/05/2022* - 3.4.6
- FIX : script de migration des messages llx_ticket_msg vers llx_actioncomm *17/05/2022* - 3.4.5
- FIX : script de migration des notes de frais et compatibilité ndfp / ndfp_rh  *11/04/2022* - 3.4.4
- FIX : Avoid emptying token value when clear filters in list *01/04/2022* - 3.4.3
- FIX : Database integrity script update  *17/03/2022* - 3.4.2
- FIX : Ajout d'une compatibilité pour le dépôt git ndfp et pas seulement ndfp_rh  *15/03/2022* - 3.4.1
- NEW : Ajout du script de migration des notes de frais du module NDFP+ vers standard Dolibarr  *10/03/2022* - 3.4

## RELEASE 3.3 - 14/01/2022

- NEW : Script for set to 1 encrypting of the password in multicompany admin/security conf  *14/01/2022* - 3.3

## RELEASE 3.2 - 29/06/2021
- FIX : fatal php8.1 remove &$GLOBAL to $GLOBAL - *31/05/2022)* - 3.2.9  
- FIX : Script for MVD add missing substitutions  *08/07/2021* - 3.2.8
- FIX : Script for MVD add missing substitutions  *07/07/2021* - 3.2.7
- FIX : Bad fix for pgsql ... *29/06/2021* - 3.2.6
- FIX : module family *24/06/2021* - 3.2.5
- NEW : Compatibility V14 - script which aim at replace __REFCLIENT__ with __REF_CLIENT__ *09/06/2021* - 3.2.4
- FIX : Listview search input checkbox sql query *30/04/2021* - 3.2.3
- FIX : Missing help tweak on setup_print_input_form_part lib *26/03/2021* - 3.2.2
- FIX : Listview context detection for columns *04/01/2021*
- NEW : Listview tooltip param  *15/12/2020*
  Allow usage of tooltip key in listview params to add tooltips for cols

___
## RELEASE 3.0 - 06/03/2019

- FIX : Dolibarr 9.x compatiblity
