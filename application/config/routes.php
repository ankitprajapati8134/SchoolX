<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'admin_login';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
$route['sync_offline_data'] = 'SyncOfflineData/index';
$route['accounts'] = 'Account/fetch_accounts';
$route['create_account'] = 'Account/create_account';
$route['edit_account/(:any)'] = 'Account/edit_account/$1';
$route['delete_account/(:any)'] = 'Account/delete_account/$1';
$route['calculate_balances'] = 'Account/calculate_balances';
$route['account/populateTable'] = 'account/populateTable';
$route['accounts/get'] = 'AccountController/getAccounts';
$route['fees/lookup_student']    = 'fees/lookup_student';
$route['fees/fetch_fee_details'] = 'fees/fetch_fee_details';

// Result Management
$route['result']                                          = 'result/index';
$route['result/template_designer']                        = 'result/template_designer';
$route['result/template_designer/(:any)']                 = 'result/template_designer/$1';
$route['result/marks_entry']                              = 'result/marks_entry';
$route['result/marks_entry/(:any)']                       = 'result/marks_entry/$1';
$route['result/marks_sheet/(:any)/(:any)/(:any)/(:any)']  = 'result/marks_sheet/$1/$2/$3/$4';
$route['result/class_result']                             = 'result/class_result';
$route['result/class_result/(:any)']                      = 'result/class_result/$1';
$route['result/student_result/(:any)']                    = 'result/student_result/$1';
$route['result/report_card/(:any)/(:any)']                = 'result/report_card/$1/$2';
$route['result/cumulative']                               = 'result/cumulative';
$route['result/save_template']                            = 'result/save_template';
$route['result/get_template']                             = 'result/get_template';
$route['result/save_marks']                               = 'result/save_marks';
$route['result/get_marks']                                = 'result/get_marks';
$route['result/compute_results']                          = 'result/compute_results';
$route['result/get_class_result_data']                    = 'result/get_class_result_data';
$route['result/get_cumulative_data']                      = 'result/get_cumulative_data';
$route['result/save_cumulative_config']                   = 'result/save_cumulative_config';
$route['result/compute_cumulative']                       = 'result/compute_cumulative';
$route['result/get_exam_status']                          = 'result/get_exam_status';




