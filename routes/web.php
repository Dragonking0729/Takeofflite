<?php

use App\Http\Controllers\AssemblyController;
use App\Http\Controllers\CostGroupController;
use App\Http\Controllers\ProposalGroupController;
use App\Http\Controllers\AddOnController;
use App\Http\Controllers\InvoiceGroupController;
use App\Http\Controllers\CostItemController;
use App\Http\Controllers\ProposalItemController;
use App\Http\Controllers\InvoiceItemController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TakeoffQuestion;
use App\Http\Controllers\ProposalTextController;
use App\Http\Controllers\InvoiceTextController;
use App\Http\Controllers\FormulaController;
use App\Http\Controllers\SheetController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\EstimateController;
use App\Http\Controllers\DocumentsController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\JobPortalController;
use App\Http\Controllers\FlipController;
use App\Http\Controllers\DailyLogsController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\MembershipController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


// Dashboard route
Route::redirect('/token/{token}', '/dashboard')->middleware('web', 'checkIsUserAuthorized');
Route::group(['middleware' => ['web', 'checkUserSession']], function () {
    Route::redirect('', 'dashboard');
    Route::resources([
        'dashboard' => DashboardController::class,
        'costgroup' => CostGroupController::class,
        'costitem' => CostItemController::class,
        'add_on' => AddOnController::class,
        'proposal_group' => ProposalGroupController::class,
        'proposal_item' => ProposalItemController::class,
        'proposal_text' => ProposalTextController::class,
        'invoice_group' => InvoiceGroupController::class,
        'invoice_item' => InvoiceItemController::class,
        'invoice_text' => InvoiceTextController::class,
        'assembly' => AssemblyController::class,
        'question' => TakeoffQuestion::class,
        'formula' => FormulaController::class
    ]);
    Route::post('dashboard/update_company', [DashboardController::class, 'update_company']);
    Route::post('dashboard/get_project_detail', [DashboardController::class, 'get_project_detail'])->name('dashboard.get_project_detail');
    Route::post('dashboard/get_customer_token', [DashboardController::class, 'get_customer_token'])->name('dashboard.get_customer_token');
    Route::post('dashboard/store_customer_email', [DashboardController::class, 'store_customer_email'])->name('dashboard.store_customer_email');
    Route::post('dashboard/job_share', [DashboardController::class, 'job_share'])->name('dashboard.job_share');

    // costgroup routes
    Route::post('costgroup/fetch', [CostGroupController::class, 'fetch']); // // get data by next/prev
    Route::post('costgroup/getdata', [CostGroupController::class, 'getdata']); // get costgroup data after add/update/delete
    Route::post('costgroup/update_desc_folder', [CostGroupController::class, 'update_desc_folder']);
    Route::post('costgroup/renumbering', [CostGroupController::class, 'renumbering']);
    Route::post('costgroup/get_costgroup_tree', [CostGroupController::class, 'get_costgroup_tree']);
    Route::post('costgroup/get_costgroup_by_id', [CostGroupController::class, 'get_costgroup_by_id']);

    // costitem routes
    Route::post('costitem/fetch', [CostItemController::class, 'fetch']); // // get data by next/prev
    Route::post('costitem/get_costitem_by_id', [CostItemController::class, 'get_costitem_by_id']);
    Route::post('costitem/update_costitem', [CostItemController::class, 'update_costitem']);
    Route::post('costitem/renumbering', [CostItemController::class, 'renumbering']);
    Route::post('costitem/new_question', [CostItemController::class, 'new_question']);
    Route::post('costitem/store_formula', [CostItemController::class, 'store_formula']);

    // assembly routes
    Route::post('assembly/fetch', [AssemblyController::class, 'fetch']); // // get data by next/prev
    Route::post('assembly/get_assembly_by_id', [AssemblyController::class, 'get_assembly_by_id']); // get assembly by id
    Route::post('assembly/add_assembly', [AssemblyController::class, 'add_assembly']); // add assembly and items
    Route::post('assembly/delete_assembly', [AssemblyController::class, 'delete_assembly']); // delete assembly
    Route::post('assembly/update_assembly', [AssemblyController::class, 'update_assembly']); // update assembly desc and items
    Route::post('assembly/ungrouping_assembly', [AssemblyController::class, 'ungrouping_assembly']); // ungrouping
    Route::post('assembly/grouping_assembly', [AssemblyController::class, 'grouping_assembly']); // grouping
    Route::post('assembly/update_qv_field', [AssemblyController::class, 'update_qv_field']); // update qv
    Route::post('assembly/update_assembly_number', [AssemblyController::class, 'update_assembly_number']); // renumber
    Route::post('assembly/delete_assembly_item', [AssemblyController::class, 'delete_assembly_item']); // delete assembly item
    Route::post('assembly/update_assembly_item_order', [AssemblyController::class, 'update_assembly_item_order']); // update assembly item order
    Route::post('assembly/get_assembly_item_formula', [AssemblyController::class, 'get_assembly_item_formula']); // get assembly item formula
    Route::post('assembly/save_assembly_item_formula', [AssemblyController::class, 'save_assembly_item_formula']); // save assembly item formula
    Route::post('assembly/bulk_delete_assembly_items', [AssemblyController::class, 'bulk_delete_assembly_items']);

    // proposal_group routes
    Route::post('proposal_group/fetch', [ProposalGroupController::class, 'fetch']);
    Route::post('proposal_group/getdata', [ProposalGroupController::class, 'getdata']);
    Route::post('proposal_group/update_desc_folder', [ProposalGroupController::class, 'update_desc_folder']);
    Route::post('proposal_group/renumbering', [ProposalGroupController::class, 'renumbering']);
    Route::post('proposal_group/get_proposalgroup_tree', [ProposalGroupController::class, 'get_proposalgroup_tree']);
    Route::post('proposal_group/get_proposalgroup_by_id', [ProposalGroupController::class, 'get_proposalgroup_by_id']);

    // add_on routes
    Route::post('add_on/fetch', [AddOnController::class, 'fetch']); // get data by next/prev
    Route::post('add_on/getdata', [AddOnController::class, 'getdata']); // get data after add/update/delete
    Route::post('add_on/update', [AddOnController::class, 'update']);
    Route::post('add_on/get_addon_tree', [AddOnController::class, 'get_addon_tree']);
    Route::post('add_on/get_addon_by_id', [AddOnController::class, 'get_addon_by_id']);

    // proposal_item routes
    Route::post('proposal_item/fetch', [ProposalItemController::class, 'fetch']); // // get data by next/prev
    Route::post('proposal_item/get_proposalitem_by_id', [ProposalItemController::class, 'get_proposalitem_by_id']);
    Route::post('proposal_item/update_proposalitem', [ProposalItemController::class, 'update_proposalitem']);
    Route::post('proposal_item/renumbering', [ProposalItemController::class, 'renumbering']);
    Route::post('proposal_item/new_question', [ProposalItemController::class, 'new_question']);
    Route::post('proposal_item/store_formula', [ProposalItemController::class, 'store_formula']);

    // proposal text routes
    Route::post('proposal_text/fetch', [ProposalTextController::class, 'fetch']);
    Route::post('proposal_text/getdata', [ProposalTextController::class, 'getdata']);
    Route::post('proposal_text/update', [ProposalTextController::class, 'update']);
    Route::post('proposal_text/get_proposal_text_tree', [ProposalTextController::class, 'get_proposal_text_tree']);
    Route::post('proposal_text/get_proposal_text_by_id', [ProposalTextController::class, 'get_proposal_text_by_id']);

    // invoice_group routes
    Route::post('invoice_group/fetch', [InvoiceGroupController::class, 'fetch']);
    Route::post('invoice_group/getdata', [InvoiceGroupController::class, 'getdata']);
    Route::post('invoice_group/update_desc_folder', [InvoiceGroupController::class, 'update_desc_folder']);
    Route::post('invoice_group/renumbering', [InvoiceGroupController::class, 'renumbering']);
    Route::post('invoice_group/get_invoicegroup_tree', [InvoiceGroupController::class, 'get_invoicegroup_tree']);
    Route::post('invoice_group/get_invoicegroup_by_id', [InvoiceGroupController::class, 'get_invoicegroup_by_id']);

    // invoice_item routes
    Route::post('invoice_item/fetch', [InvoiceItemController::class, 'fetch']); // // get data by next/prev
    Route::post('invoice_item/get_invoiceitem_by_id', [InvoiceItemController::class, 'get_invoiceitem_by_id']);
    Route::post('invoice_item/update_invoiceitem', [InvoiceItemController::class, 'update_invoiceitem']);
    Route::post('invoice_item/renumbering', [InvoiceItemController::class, 'renumbering']);
    Route::post('invoice_item/new_question', [InvoiceItemController::class, 'new_question']);
    Route::post('invoice_item/store_formula', [InvoiceItemController::class, 'store_formula']);

    // invoice text routes
    Route::post('invoice_text/fetch', [InvoiceTextController::class, 'fetch']);
    Route::post('invoice_text/getdata', [InvoiceTextController::class, 'getdata']);
    Route::post('invoice_text/update', [InvoiceTextController::class, 'update']);
    Route::post('invoice_text/get_invoice_text_tree', [InvoiceTextController::class, 'get_invoice_text_tree']);
    Route::post('invoice_text/get_invoice_text_by_id', [InvoiceTextController::class, 'get_invoice_text_by_id']);

    // question routes
    Route::post('question/fetch', [TakeoffQuestion::class, 'fetch']); // get data by next/prev
    Route::post('question/getdata', [TakeoffQuestion::class, 'getdata']); // get question data after add/update/delete
    Route::post('question/update', [TakeoffQuestion::class, 'update']);
    Route::post('question/get_question_tree', [TakeoffQuestion::class, 'get_question_tree']);
    Route::post('question/get_question_by_id', [TakeoffQuestion::class, 'get_question_by_id']);

    // stored formula routes
    Route::post('formula/fetch', [FormulaController::class, 'fetch']);
    Route::post('formula/get_stored_formula_tree', [FormulaController::class, 'get_stored_formula_tree']);
    Route::post('formula/get_formula_by_id', [FormulaController::class, 'get_formula_by_id']);
    Route::post('formula/get_data', [FormulaController::class, 'get_data']); // get question data after add/update/delete
    Route::post('formula/update', [FormulaController::class, 'update']);

    // membership routes
    Route::get('membership', [MembershipController::class, 'index'])->name('membership.index');
    Route::post('manage_billing', [MembershipController::class, 'manage_billing'])->name('membership.manage_billing');
});

// project route
Route::group(['middleware' => ['web', 'checkUserSession']], function () {

    Route::resources([
        'estimate' => EstimateController::class,
        'documents' => DocumentsController::class,
        'invoice' => InvoiceController::class,
        'proposal' => ProposalController::class,
        'flip' => FlipController::class,
        'daily_logs' => DailyLogsController::class,
    ]);


    /**
     * estimate route
     */
    Route::post('estimate/add_item_to_ss', [EstimateController::class, 'add_item_to_ss']); // add item to spreadsheet
    Route::post('estimate/add_addon_to_ss', [EstimateController::class, 'add_addon_to_ss']); // add addon to spreadsheet
    Route::post('estimate/delete_addon_to_ss', [EstimateController::class, 'delete_addon_to_ss']); // delete addon to spreadsheet
    Route::post('estimate/update_addon_to_ss', [EstimateController::class, 'update_addon_to_ss']); // update addon to spreadsheet
    Route::post('estimate/add_assembly_item_to_ss', [EstimateController::class, 'add_assemblyItems_to_ss']); // add item to spreadsheet
    Route::post('estimate/update_ss_items', [EstimateController::class, 'update_ss_items']); // update ss item
    Route::post('estimate/update_all_toq_ss', [EstimateController::class, 'update_all_toq_ss']); // update all TOQ occurrence
    Route::post('estimate/update_all_price_ss', [EstimateController::class, 'update_all_price_ss']); // update all prices occurrence
    Route::post('estimate/sort_ss', [EstimateController::class, 'sort_ss']); // sort ss item
    Route::post('estimate/remove_bulk_ss_items', [EstimateController::class, 'remove_bulk_ss_items']);
    Route::post('estimate/update_ss_setting', [EstimateController::class, 'update_ss_setting']); // update ss setting
    Route::post('estimate/update_ss_sidebar_status', [EstimateController::class, 'update_ss_sidebar_status']); // update ss sidebar status
    Route::post('estimate/get_price', [EstimateController::class, 'get_price']); // update ss setting
    Route::post('estimate/get_formula', [EstimateController::class, 'get_formula']);
    Route::post('estimate/get_formula_assembly', [EstimateController::class, 'get_formula_assembly']);
    Route::post('estimate/add_item_to_ss_by_interview', [EstimateController::class, 'add_item_to_ss_by_interview']);
    Route::post('estimate/add_assembly_to_ss_interview', [EstimateController::class, 'add_assembly_to_ss_interview']);


    /**
     * documents route
     */
    Route::get('documents/{project_id}/{category_id}', [DocumentsController::class, 'show']);
    Route::post('documents/file_upload', [DocumentsController::class, 'file_upload']);
    Route::post('documents/remove_sheet', [DocumentsController::class, 'remove_sheet']);
    Route::post('documents/update_sheet_name', [DocumentsController::class, 'update_sheet_name']);
    Route::post('documents/update_sheet_order', [DocumentsController::class, 'update_sheet_order']);
    Route::post('documents/remove_multiple_sheets', [DocumentsController::class, 'remove_multiple_sheets']);
    Route::post('documents/update_documents_sidebar_status', [DocumentsController::class, 'update_documents_sidebar_status'])->name('documents.update_documents_sidebar_status');


    /**
     * sheet route
     */
    Route::get('sheet/{project_id}/{sheet_id}/{segment_id?}', [SheetController::class, 'show']);
    Route::post('sheet/set_scale', [SheetController::class, 'set_scale']);
    Route::post('sheet/remove_all_segments', [SheetController::class, 'remove_all_segments']);
    Route::post('sheet/create_measurement', [SheetController::class, 'create_measurement']);
    Route::post('sheet/add_point', [SheetController::class, 'add_point']);
    Route::post('sheet/remove_object', [SheetController::class, 'remove_object']);
    Route::post('sheet/remove_orphaned_measurement', [SheetController::class, 'remove_orphaned_measurement']);
    Route::post('sheet/move_object', [SheetController::class, 'move_object']);
    Route::post('sheet/add_line', [SheetController::class, 'add_line']);
    Route::post('sheet/update_measurement', [SheetController::class, 'update_measurement']);
    Route::post('sheet/add_area', [SheetController::class, 'add_area']);
    Route::post('sheet/get_measurement_TOQ', [SheetController::class, 'get_measurement_TOQ']);
    Route::post('sheet/update_line', [SheetController::class, 'update_line']);
    Route::post('sheet/update_area', [SheetController::class, 'update_area']);
    Route::post('sheet/add_polyline', [SheetController::class, 'add_polyline']);
    Route::post('sheet/update_zoom', [SheetController::class, 'update_zoom'])->name('sheet.update_zoom');
    Route::post('sheet/update_sheet_sidebar_status', [SheetController::class, 'update_sheet_sidebar_status'])->name('sheet.update_sheet_sidebar_status');


    /**
     * invoice route
     */
    Route::post('invoice/remove_bulk_invoice_items', [InvoiceController::class, 'remove_bulk_invoice_items'])->name('invoice.remove_bulk_invoice_items');
    Route::post('invoice/remove_single_invoice_line', [InvoiceController::class, 'remove_single_invoice_line'])->name('invoice.remove_single_invoice_line');
    Route::post('invoice/add_invoice_line_by_tree', [InvoiceController::class, 'add_invoice_line_by_tree'])->name('invoice.add_invoice_line_by_tree');
    Route::post('invoice/update_invoice_line_order', [InvoiceController::class, 'update_invoice_line_order'])->name('invoice.update_invoice_line_order');
    Route::post('invoice/update_invoice_line_field', [InvoiceController::class, 'update_invoice_line_field'])->name('invoice.update_invoice_line_field');
    Route::post('invoice/send_invoice_email', [InvoiceController::class, 'send_invoice_email'])->name('invoice.send_invoice_email');
    Route::post('invoice/update_invoice_info', [InvoiceController::class, 'update_invoice_info'])->name('invoice.update_invoice_info');
    Route::get('invoice/{invoice}/{invoice_id_param?}', [InvoiceController::class, 'show'])->name('invoice.show');
    Route::post('invoice/remove_invoice', [InvoiceController::class, 'remove_invoice'])->name('invoice.remove_invoice');
    Route::post('invoice/update_lock', [InvoiceController::class, 'update_lock'])->name('invoice.update_lock');
    Route::post('invoice/get_document_text', [InvoiceController::class, 'get_document_text'])->name('invoice.get_document_text');
    Route::post('invoice/update_invoice_sidebar_status', [InvoiceController::class, 'update_invoice_sidebar_status'])->name('invoice.update_invoice_sidebar_status');


    /**
     * proposal route
     */
    Route::post('proposal/remove_bulk_proposal_items', [ProposalController::class, 'remove_bulk_proposal_items'])->name('proposal.remove_bulk_proposal_items');
    Route::post('proposal/remove_single_proposal_line', [ProposalController::class, 'remove_single_proposal_line'])->name('proposal.remove_single_proposal_line');
    Route::post('proposal/add_proposal_line_by_tree', [ProposalController::class, 'add_proposal_line_by_tree'])->name('proposal.add_proposal_line_by_tree');
    Route::post('proposal/add_proposal_from_cost_items', [ProposalController::class, 'add_proposal_from_cost_items'])->name('proposal.add_proposal_from_cost_items');
    Route::post('proposal/update_proposal_line_order', [ProposalController::class, 'update_proposal_line_order'])->name('proposal.update_proposal_line_order');
    Route::post('proposal/update_proposal_line_field', [ProposalController::class, 'update_proposal_line_field'])->name('proposal.update_proposal_line_field');
    Route::post('proposal/send_proposal_email', [ProposalController::class, 'send_proposal_email'])->name('proposal.send_proposal_email');
    Route::post('proposal/update_proposal_info', [ProposalController::class, 'update_proposal_info'])->name('proposal.update_proposal_info');
    Route::get('proposal/{proposal}/{proposal_id_param?}', [ProposalController::class, 'show'])->name('proposal.show');
    Route::post('proposal/remove_proposal', [ProposalController::class, 'remove_proposal'])->name('proposal.remove_proposal');
    Route::post('proposal/update_lock', [ProposalController::class, 'update_lock'])->name('proposal.update_lock');
    Route::post('proposal/update_status_manually', [ProposalController::class, 'update_status_manually'])->name('proposal.update_status_manually');
    Route::post('proposal/get_document_text', [ProposalController::class, 'get_document_text'])->name('proposal.get_document_text');
    Route::post('proposal/update_proposal_sidebar_status', [ProposalController::class, 'update_proposal_sidebar_status'])->name('proposal.update_proposal_sidebar_status');


    /**
     * flip route
     */
    Route::post('flip/update_entry', [FlipController::class, 'update_entry'])->name('flip.update_entry');
    Route::post('flip/update_flip_detail', [FlipController::class, 'update_flip_detail'])->name('flip.update_flip_detail');


    /**
     * daily logs route
     */
    Route::post('daily_logs/remove_logs', [DailyLogsController::class, 'remove_logs'])->name('daily_logs.remove_logs');
    Route::post('daily_logs/update_log_line', [DailyLogsController::class, 'update_log_line'])->name('daily_logs.update_log_line');
    Route::post('daily_logs/attach_more_files', [DailyLogsController::class, 'attach_more_files'])->name('daily_logs.attach_more_files');
});


Route::get('email/view', [CustomerPortalController::class, 'email_view'])->name('email.email_view');

// customer portal route
Route::get('/customer/token/{token}', [CustomerPortalController::class, 'check_customer'])->middleware('checkIsCustomerAuthorized')->name('customer.check_customer');
Route::post('/customer/check_passcode', [CustomerPortalController::class, 'check_passcode'])->name('customer.check_passcode');

Route::group(['middleware' => ['web', 'checkCustomerSession']], function () {
    Route::get('customer', [CustomerPortalController::class, 'show'])->name('customer.show');
    Route::post('customer/get_preview_content', [CustomerPortalController::class, 'get_preview_content'])->name('customer.get_preview_content');
    Route::post('customer/approve_proposal', [CustomerPortalController::class, 'approve_proposal'])->name('customer.approve_proposal');
});

// job portal route
Route::get('/job', [JobPortalController::class, 'index'])->name('job.index');
Route::get('/job/{user_id}/{project_id}', [JobPortalController::class, 'show'])->name('job.show');
