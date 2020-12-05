<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::group(['prefix' => 'secure'], function () {
    //REPORTS
    Route::get('reports/envato/earnings', 'ReportsController@envatoEarnings');
    Route::get('reports/tickets/count/daily', 'ReportsController@dailyTicketCount');
    Route::get('reports/tickets/range', 'ReportsController@generateTicketsReport');
    Route::get('reports/help-center', 'ReportsController@helpCenterReport');
    Route::get('reports/user/{userId}/searches', 'ReportsController@userSearches');

    //USER TAGS
    Route::post('users/{id}/tags/sync', 'UserTagsController@sync');

    //USER DETAILS
    Route::put('users/{id}/details', 'UserDetailsController@update');

    //USER EMAILS
    Route::post('users/{id}/emails/attach', 'UserEmailsController@attach');
    Route::post('users/{id}/emails/detach', 'UserEmailsController@detach');

    //TICKETS
    Route::get('tickets', 'TicketController@index');
    Route::get('tickets/{tagId}/next-active-ticket', 'TicketController@nextActiveTicket');
    Route::post('tickets', 'TicketController@store');
    Route::put('tickets/{id}', 'TicketController@update');
    Route::post('tickets/merge/{ticket1}/{ticket2}', 'TicketsMergeController@merge');
    Route::get('tickets/{id}', 'TicketController@show');
    Route::post('tickets/assign', 'TicketAssigneeController@change');
    Route::delete('tickets', 'TicketController@destroy');
    Route::get('tickets/{id}/replies', 'TicketRepliesController@index');
    Route::post('tickets/{id}/{type}', 'TicketRepliesController@store')->where('type', 'drafts|replies|notes');

    //SEARCH TERM
    Route::post('search-term', 'SearchTermController@store');

    //AGENT SEARCH
    Route::get('search/all', 'SearchController@all');
    Route::get('search/users', 'SearchController@users');
    Route::get('search/tickets', 'SearchController@tickets');
    Route::get('search/articles', 'SearchController@articles');

    //REPLIES
    Route::get('replies/{id}', 'RepliesController@show');
    Route::get('replies/{id}/original', 'OriginalReplyEmailController@show');
    Route::put('replies/{id}', 'RepliesController@update');
    Route::delete('replies/{id}', 'RepliesController@destroy');

    //DRAFTS
    Route::delete('drafts/{id}', 'DraftsController@destroy');
    Route::post('drafts/{draftId}/uploads/{uploadId}/detach', 'DraftUploadsController@detach');

    //TICKET TAGS
    Route::post('tickets/status/change', 'TicketStatusController@change');
    Route::post('tickets/tags/add', 'TicketTagsController@add');
    Route::post('tickets/tags/remove', 'TicketTagsController@remove');

    //TAGS
    Route::get('tags/agent-mailbox', 'TagController@tagsForAgentMailbox');
    Route::post('tags', 'TagController@store');
    Route::put('tags/{id}', 'TagController@update');
    Route::delete('tags/delete-multiple', 'TagController@deleteMultiple');

    //NEW TICKET CATEGORIES
    Route::get('new-ticket/categories', 'NewTicketCategoriesController@index');

    //CANNED REPLIES
    Route::get('canned-replies', 'CannedRepliesController@index');
    Route::post('canned-replies', 'CannedRepliesController@store');
    Route::put('canned-replies/{id}', 'CannedRepliesController@update');
    Route::delete('canned-replies', 'CannedRepliesController@destroy');

    //HELP CENTER
    Route::get('help-center', 'HelpCenterController@index');
    Route::get('help-center/sidenav', 'HelpCenterController@sidenav');

    //HELP CENTER CATEGORIES
    Route::get('help-center/categories', 'CategoryController@index');
    Route::get('help-center/categories/{id}', 'CategoryController@show');
    Route::post('help-center/categories', 'CategoryController@store');
    Route::post('help-center/categories/reorder', 'CategoriesOrderController@change');
    Route::put('help-center/categories/{id}', 'CategoryController@update');
    Route::post('help-center/categories/{id}/detach-parent', 'ChildCategoryController@detachParent');
    Route::delete('help-center/categories/{id}', 'CategoryController@destroy');

    //HELP CENTER ARTICLES
    Route::get('help-center/articles/{id}', 'ArticleController@show');
    Route::get('help-center/articles/{article}/download/{hashes}', 'ArticleAttachmentsController@download');
    Route::get('help-center/articles', 'ArticleController@index');
    Route::post('help-center/articles', 'ArticleController@store');
    Route::put('help-center/articles/{id}', 'ArticleController@update');
    Route::post('help-center/articles/{id}/feedback', 'ArticleFeedbackController@submit');
    Route::post('images/static/upload', 'StaticImagesController@upload');
    Route::delete('help-center/articles', 'ArticleController@destroy');

    //TRIGGERS
    Route::get('triggers', 'TriggersController@index');
    Route::get('triggers/conditions', 'ConditionsController@index');
    Route::get('triggers/actions', 'ActionsController@index');
    Route::get('triggers/value-options/{name}', 'TriggerValueOptionsController@show');
    Route::get('triggers/{id}', 'TriggersController@show');
    Route::post('triggers', 'TriggersController@store');
    Route::put('triggers/{id}', 'TriggersController@update');
    Route::delete('triggers', 'TriggersController@destroy');

    //ENVATO
    Route::get('envato/validate-purchase-code', 'EnvatoController@validateCode');
    Route::post('envato/add-purchase-using-code', 'EnvatoController@addPurchaseUsingCode');
    Route::post('envato/items/import', 'EnvatoController@ImportItems');

    //HElP CENTER IMPORT/EXPORT
    Route::post('help-center/actions/import', 'HelpCenterActionsController@import');
    Route::get('help-center/actions/export', 'HelpCenterActionsController@export');
    Route::post('help-center/actions/delete-unused-images', 'HelpCenterActionsController@deleteUnusedImages');

    //3RD PARTY IMPORT/EXPORT
    Route::get('ticketing/actions/helpscout/import', 'TicketingActionsController@importHelpScoutConversations');

    // APP\NOTIFICATIONSUBSCRIPTION
    Route::apiResource('notification-subscription', 'NotificationSubscriptionController');
});

//TICKETS MAIL WEBHOOKS
Route::post('tickets/mail/incoming', 'TicketsMailController@handleIncoming');
Route::post('tickets/mail/failed', 'TicketsMailController@handleFailed');

//FRONT-END ROUTES THAT NEED TO BE PRE-RENDERED
Route::get('/', 'HelpCenterController@index')->middleware('prerenderIfCrawler');
Route::get('help-center', 'HelpCenterController@index')->middleware('prerenderIfCrawler');
Route::get('help-center/articles/{articleId}/{slug}', 'ArticleController@show')->middleware('prerenderIfCrawler');
Route::get('help-center/articles/{parentId}/{articleId}/{slug}', 'ArticleController@show')->middleware('prerenderIfCrawler');
Route::get('help-center/articles/{parentId}/{childId}/{articleId}/{slug}', 'ArticleController@show')->middleware('prerenderIfCrawler');
Route::get('help-center/categories/{categoryId}/{slug}', 'CategoryController@show')->middleware('prerenderIfCrawler');
Route::get('help-center/search/{query}', 'SearchController@articles')->middleware('prerenderIfCrawler');

//CATCH ALL ROUTES AND REDIRECT TO HOME
Route::get('{all}', '\Common\Core\Controllers\HomeController@show')->where('all', '.*');
