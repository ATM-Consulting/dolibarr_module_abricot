# Abricot - Changelog
All notable changes to this project will be documented in this file.
___

# NOT RELEASED

## RELEASE 3.9
- FIX: Params missing GETPOST - *23/06/2025* - 3.9.5
- FIX: Database connection from external module - *18/06/2025* - 3.9.4
- FIX: Compatibility v21 - *17/12/2024* - 3.9.3
- FIX: Replace `is_callable('parent::method')` with `is_callable(parent::class.'::method')` to avoid deprecation warning on PHP 8.2 - *27/11/2024* - 3.9.2
- FIX: TResponseMail constructor, class properties were not defined with old PHP constructor - *30/07/2024* - 3.9.1
- FIX: Compatibility v20
  Changed Dolibarr compatibility range to 16 min - 20 max - *04/08/2024* - 3.9.0

## RELEASE 3.8
- FIX: PHP 8.2 warning undefined array key visible - *13/03/2024* - 3.8.3
- FIX: PHP 8.2 warning undefined array key visible - *14/12/2023* - 3.8.2
- FIX: PHP 8.2 warning - *11/12/2023* - 3.8.1
- NEW: Dolibarr 19 compatibility - *22/11/2023* - 3.8.0
  Warning: loss of compatibility with Dolibarr versions below 6

## RELEASE 3.7
- NEW: TObjetStd add nullable property to integer fields - *21/11/2023* - 3.7.0

## RELEASE 3.6
- NEW: Pre-configuration script (initialization/replacement of certain constants) during installs/version upgrades - *19/10/2023* - 3.6.0
- FIX: Handling separation of SQL queries, SQL error during activation of productbycompany module - *7/12/2023* - 3.6.1

## RELEASE 3.5
- FIX: PHP8: date string versus timestamp mixup - *05/09/2023* - 3.5.10
- FIX: PHP8: warnings - *30/08/2023* - 3.5.9
- FIX: Consideration of the title parameter in the setup_print_title function - *29/08/2023* - 3.5.8
- FIX: setup_print_title function correction - *03/08/2023* - 3.5.7
- FIX: Query escape column names and MySQL/PgSQL compatibility - *24/03/2023* - 3.5.6
- FIX: Substitution script include ticket ref - *3.5.5* - **27/02/2023**
- FIX: Migration script for ticketsup takes into account the ref in actioncomm + handling extrafields error - *21/12/2022* - 3.5.4
- FIX: Backward compatibility - *18/11/2022* - 3.5.3
- FIX: Fix various PHP8 warnings - *16/08/2022* - 3.5.2
- FIX: Change editor name: ATM-Consulting -> ATM Consulting - *3.5.1* - **09/08/2022**
- NEW: Added a filter on listview allowing multiselect to sort with multiple tags - *27/07/2022* - 3.5.0

## RELEASE 3.4 - 11/03/2022
- FIX: Fatal inclusion lib fail - *09/08/2022* - 3.4.10
- FIX: V16 FAMILY - *02/06/2022* - 3.4.9
- FIX: V16 NewToken() - *02/06/2022* - 3.4.8
- FIX: Correction of VAT retrieval in migration script from ndfp to standard expense reports - *17/05/2022* - 3.4.7
- FIX: Old Icon - *21/05/2022* - 3.4.6
- FIX: Migration script for messages llx_ticket_msg to llx_actioncomm - *17/05/2022* - 3.4.5
- FIX: Migration script for expense reports and compatibility ndfp / ndfp_rh - *11/04/2022* - 3.4.4
- FIX: Avoid emptying token value when clearing filters in list - *01/04/2022* - 3.4.3
- FIX: Database integrity script update - *17/03/2022* - 3.4.2
- FIX: Added compatibility for ndfp git repository, not just ndfp_rh - *15/03/2022* - 3.4.1
- NEW: Added migration script for expense reports from NDFP+ to standard Dolibarr - *10/03/2022* - 3.4

## RELEASE 3.3 - 14/01/2022
- NEW: Script to set encrypting of the password to 1 in multicompany admin/security conf - *14/01/2022* - 3.3

## RELEASE 3.2 - 29/06/2021
- FIX: Fatal PHP 8.1 remove &$GLOBAL to $GLOBAL - *31/05/2022* - 3.2.9
- FIX: Script for MVD add missing substitutions - *08/07/2021* - 3.2.8
- FIX: Script for MVD add missing substitutions - *07/07/2021* - 3.2.7
- FIX: Bad fix for PgSQL - *29/06/2021* - 3.2.6
- FIX: Module family - *24/06/2021* - 3.2.5
- NEW: Compatibility V14 - script to replace __REFCLIENT__ with __REF_CLIENT__ - *09/06/2021* - 3.2.4
- FIX: Listview search input checkbox SQL query - *30/04/2021* - 3.2.3
- FIX: Missing help tweak on setup_print_input_form_part lib - *26/03/2021* - 3.2.2
- FIX: Listview context detection for columns - *04/01/2021*
- NEW: Listview tooltip param - *15/12/2020*
  Allow usage of tooltip key in listview params to add tooltips for cols

___
## RELEASE 3.0 - 06/03/2019
- FIX: Dolibarr 9.x compatibility
