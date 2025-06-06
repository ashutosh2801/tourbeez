<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AddonController;
use App\Http\Controllers\AizUploadController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\ExclusionController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\InclusionController;
use App\Http\Controllers\ItineraryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PickupController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\TaxesFeeController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\TourTypeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SubCateoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard',[ProfileController::class,'dashboard'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('/user',UserController::class);
    Route::resource('/customers',CustomerController::class);
    Route::resource('/role',RoleController::class);
    Route::resource('/permission',PermissionController::class);
    Route::resource('/category',CategoryController::class);
    Route::resource('/tour_type',TourTypeController::class);
    Route::resource('/collection',CollectionController::class);
    Route::resource('/orders', OrderController::class);
    
    // Country
    Route::resource('/countries', CountryController::class);
    Route::post('/countries/status', [CountryController::class, 'updateStatus'])->name('countries.status');
    Route::get('/countries/destroy/{id}', [CountryController::class, 'destroy'])->name('countries.destroy');

    // State
    Route::resource('/states', StateController::class);
    Route::get('/states/destroy/{id}', [StateController::class, 'destroy'])->name('states.destroy');

    // City
    Route::resource('/cities', CityController::class);
    Route::get('/cities/destroy/{id}', [CityController::class, 'destroy'])->name('cities.destroy');

    // Addone
    Route::resource('addon',AddonController::class);
    Route::get('/addon/destroy/{id}', [AddonController::class, 'destroy'])->name('addon.destroy');
    Route::post('/addon/sort-order', [AddonController::class, 'updateOrder'])->name('addon.order');

    // Pickup
    Route::resource('pickups',PickupController::class);
    Route::get('/pickups/destroy/{id}', [PickupController::class, 'destroy'])->name('pickup.destroy');
    Route::post('/pickups/sort-order', [PickupController::class, 'updateOrder'])->name('pickup.order');

    // Tour Edit
    Route::resource('tour',TourController::class);
    Route::get('/tour/{id}/edit/addon', [TourController::class, 'editAddon'])->name('tour.edit.addone');
    Route::get('/tour/{id}/edit/scheduling', [TourController::class, 'editScheduling'])->name('tour.edit.scheduling');
    Route::get('/tour/{id}/edit/location', [TourController::class, 'editLocation'])->name('tour.edit.location');
    Route::get('/tour/{id}/edit/pickups', [TourController::class, 'editPickups'])->name('tour.edit.pickups');
    Route::get('/tour/{id}/edit/itinerary', [TourController::class, 'editItinerary'])->name('tour.edit.itinerary');
    Route::get('/tour/{id}/edit/faqs', [TourController::class, 'editFaqs'])->name('tour.edit.faqs');
    Route::get('/tour/{id}/edit/inclusions', [TourController::class, 'editInclusions'])->name('tour.edit.inclusions');
    Route::get('/tour/{id}/edit/exclusions', [TourController::class, 'editExclusions'])->name('tour.edit.exclusions');
    Route::get('/tour/{id}/edit/taxesfees', [TourController::class, 'editTaxesfees'])->name('tour.edit.taxesfees');
    Route::get('/tour/{id}/edit/gallery', [TourController::class, 'editGallery'])->name('tour.edit.gallery');
    Route::get('/tour/{id}/edit/seo', [TourController::class, 'editSeo'])->name('tour.edit.seo');
    Route::get('/tour/{id}/edit/notification', [TourController::class, 'editNotification'])->name('tour.edit.message.notification');
    Route::get('/tour/{id}/edit/reminder', [TourController::class, 'editReminder'])->name('tour.edit.message.reminder');
    Route::get('/tour/{id}/edit/followup', [TourController::class, 'editFollowup'])->name('tour.edit.message.followup');
    Route::get('/tour/{id}/edit/paymentrequest', [TourController::class, 'editPaymentRequest'])->name('tour.edit.message.paymentrequest');

    // Tour Preview
    Route::get('/tour/clone/{id}', [TourController::class, 'clone'])->name('tour.clone');
    Route::get('/tour/destroy/{id}', [TourController::class, 'destroy'])->name('tour.destroy');
    Route::post('/tour/basic_detail_update/{id}', [TourController::class, 'basic_detail_update'])->name('tour.basic_detail_update');
    Route::post('/tour/addon_update/{id}', [TourController::class, 'addon_update'])->name('tour.addon_update');
    Route::post('/tour/location_update/{id}', [TourController::class, 'location_update'])->name('tour.location_update');
    Route::put('/tour/pickup_update/{id}', [TourController::class, 'pickup_update'])->name('tour.pickup_update');
    Route::put('/tour/seo_update/{id}', [TourController::class, 'seo_update'])->name('tour.seo_update');
    Route::put('/tour/schedule_update/{id}', [TourController::class, 'schedule_update'])->name('tour.schedule_update');
    Route::put('/tour/itinerary_update/{id}', [TourController::class, 'itinerary_update'])->name('tour.itinerary_update');
    Route::put('/tour/faq_update/{id}', [TourController::class, 'faq_update'])->name('tour.faq_update');
    Route::put('/tour/inclusion_update/{id}', [TourController::class, 'inclusion_update'])->name('tour.inclusion_update');
    Route::put('/tour/exclusion_update/{id}', [TourController::class, 'exclusion_update'])->name('tour.exclusion_update');
    Route::put('/tour/taxfee_update/{id}', [TourController::class, 'taxfee_update'])->name('tour.taxfee_update');
    Route::put('/tour/gallery_update/{id}', [TourController::class, 'gallery_update'])->name('tour.gallery_update');
    Route::put('/tour/notification_update/{id}', [TourController::class, 'notification_update'])->name('tour.notification_update');
    Route::put('/tour/reminders_update/{id}', [TourController::class, 'reminders_update'])->name('tour.reminders_update');
    Route::put('/tour/followup_update/{id}', [TourController::class, 'followup_update'])->name('tour.followup_update');
    Route::put('/tour/payment_request_update/{id}', [TourController::class, 'payment_request_update'])->name('tour.payment_request_update');
    Route::get('/tour/preview/{id}', [TourController::class, 'preview'])->name('tour.preview');
    Route::post('/tour/addfocus/{id}', [TourController::class, 'add_focus_keyword'])->name('tour.addfocus');

    Route::resource('itineraries',ItineraryController::class);
    Route::post('/itinerary/single', [ItineraryController::class, 'single'])->name('itinerary.single');

    Route::resource('faqs',FaqController::class);
    Route::post('/faq/single', [FaqController::class, 'single'])->name('faq.single');

    Route::resource('features',FeatureController::class);
    Route::post('/feature/single', [FeatureController::class, 'single'])->name('feature.single');

    Route::resource('exclusions',ExclusionController::class);
    Route::post('/exclusions/single', [ExclusionController::class, 'single'])->name('exclusion.single');

    Route::resource('inclusions',InclusionController::class);
    Route::post('/inclusions/single', [InclusionController::class, 'single'])->name('inclusion.single');

    // Product
    Route::get('/get/subcategory',[ProductController::class,'getsubcategory'])->name('getsubcategory');
    Route::get('/remove-external-img/{id}',[ProductController::class,'removeImage'])->name('remove.image');
    
    Route::resource('taxes',TaxesFeeController::class);
    Route::get('/taxes/destroy/{id}', [TaxesFeeController::class, 'destroy'])->name('taxes.destroy');
    Route::post('/taxes/sort-order', [TaxesFeeController::class, 'updateOrder'])->name('taxes.order');

    // uploaded files
    Route::resource('/uploaded-files', AizUploadController::class);
    Route::any('/uploaded-files/add_image_info', [AizUploadController::class, 'add_image_info'])->name('uploaded-files.add_image_info');
    Route::any('/uploaded-files/file-info', [AizUploadController::class, 'file_info'])->name('uploaded-files.info');
    Route::get('/uploaded-files/destroy/{id}', [AizUploadController::class, 'destroy'])->name('uploaded-files.destroy');

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity.logs');

    // Uploader
    Route::get('/refresh-csrf', function(){ return csrf_token(); });
    Route::post('/aiz-uploader', [AizUploadController::class,'show_uploader']);
    Route::post('/aiz-uploader/upload', [AizUploadController::class,'upload']);
    Route::get('/aiz-uploader/get_uploaded_files', [AizUploadController::class,'get_uploaded_files']);
    Route::delete('/aiz-uploader/destroy/{id}', [AizUploadController::class,'destroy']);
    Route::post('/aiz-uploader/get_file_by_ids', [AizUploadController::class,'get_preview_files']);
    Route::get('/aiz-uploader/download/{id}', [AizUploadController::class,'attachment_download'])->name('download_attachment');
    Route::get('/migrate/database', [AizUploadController::class,'migrate_database']);

    // Setting
    Route::resource('/settings', SettingController::class);
    Route::post('/settings/update', [SettingController::class, 'update'])->name('settings.update');
    Route::get('/general-settings', [SettingController::class, 'general_settings'])->name('general_settings');
    Route::get('/email-settings', [SettingController::class, 'email_settings'])->name('email_settings');
    Route::post('/settings/activation/update', [SettingController::class, 'updateActivationSettings'])->name('settings.activation.update');
    
    // env Update
    Route::post('/env_key_update', [SettingController::class, 'env_key_update'])->name('env_key_update.update');
    Route::post('/settings/test/mail', [SettingController::class, 'testEmail'])->name('test.mail');

    // Email Templates
    Route::resource('/email-templates', EmailTemplateController::class);
    Route::post('/email-templates/update', [EmailTemplateController::class, 'update'])->name('email-templates.update');
    Route::post('/email-templates/preview/{id}', [EmailTemplateController::class, 'preview'])->name('email-templates.preview');
    
    Route::get('/clear-cache', function() {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('permission:cache-reset');
    })->name('clear.cache');
});
