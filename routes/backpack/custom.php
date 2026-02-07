<?php

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::get('api/article', 'App\Http\Controllers\Api\ArticleController@index');
Route::get('api/article-search', 'App\Http\Controllers\Api\ArticleController@search');

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => ['web', config('backpack.base.middleware_key', 'admin')],
    'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    // -----
    // CRUDs
    // -----
    Route::crud('landing-page', 'LandingPageCrudController');

    Route::post('ckeditor/image_upload', 'LandingPageCrudController@ckeditor_image_upload')->name('upload');
    Route::get('upazila_model/ajax-upazila-options', 'UpazilaCrudController@upazilaOptions');

    Route::crud('page', 'PageCrudController');

    Route::crud('package', 'PackageCrudController');
    Route::crud('order', 'OrderCrudController');
    Route::crud('review', 'ReviewCrudController');
    Route::crud('faq', 'FaqCrudController');

    Route::crud('user', 'UserCrudController');
    
    // -----
    // Messaging Routes
    // -----
    Route::get('message-test', 'MessageCrudController@testDatabase')->name('backpack.message.test');
    Route::get('message-crud-test', 'MessageCrudController@testCrud')->name('backpack.message.crud-test');
    Route::get('message-conversations', 'MessageCrudController@conversations')->name('backpack.message.conversations');
    Route::get('message-conversation/{userId}', 'MessageCrudController@showConversation')->name('backpack.message.conversation');
    Route::post('message-send', 'MessageCrudController@sendMessage')->name('backpack.message.send');
    Route::post('message-mark-read/{userId}', 'MessageCrudController@markMessagesAsRead')->name('backpack.message.mark-read');
    Route::get('message-messages/{userId}', 'MessageCrudController@getMessages')->name('backpack.message.messages');
    Route::get('message-cleanup-images', 'MessageCrudController@cleanupExternalImages')->name('backpack.message.cleanup-images');
    Route::crud('message', 'MessageCrudController');

    // -----
    // Bulk Email
    // -----
    Route::get('bulk-email/compose', 'BulkEmailController@compose')->name('backpack.bulk-email.compose');
    Route::post('bulk-email/send', 'BulkEmailController@send')->name('backpack.bulk-email.send');
    Route::get('bulk-email/history', 'BulkEmailController@history')->name('backpack.bulk-email.history');
    Route::get('bulk-email/campaign/{id}/log', 'BulkEmailController@showLog')->name('backpack.bulk-email.log');
    Route::post('bulk-email/campaign/{id}/retry-failed-batch', 'BulkEmailController@retryFailedBatch')->name('backpack.bulk-email.retry-failed-batch');
}); // this should be the absolute last line of this file