<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AddonController;
use App\Http\Controllers\AizUploadController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\ExclusionController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\InclusionController;
use App\Http\Controllers\ItineraryController;
use App\Http\Controllers\PickupController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TaxesFeeController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\TourTypeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SubCateoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin', 'verified'])->group(function () {
    Route::get('/dashboard',[ProfileController::class,'dashboard'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::middleware(['role:admin'])->group(function(){
        Route::resource('user',UserController::class);
        Route::resource('role',RoleController::class);
        Route::resource('permission',PermissionController::class);
        Route::resource('category',CategoryController::class);
        Route::resource('tour_type',TourTypeController::class);
        Route::resource('collection',CollectionController::class);
        //Route::resource('product',ProductController::class);

        // Addone
        Route::resource('addon',AddonController::class);
        Route::get('/addon/destroy/{id}', [AddonController::class, 'destroy'])->name('addon.destroy');
        Route::post('/addon/sort-order', [AddonController::class, 'updateOrder'])->name('addon.order');

        // Pickup
        Route::resource('pickups',PickupController::class);
        Route::get('/pickups/destroy/{id}', [PickupController::class, 'destroy'])->name('pickup.destroy');
        Route::post('/pickups/sort-order', [PickupController::class, 'updateOrder'])->name('pickup.order');

        // Tour
        Route::resource('tour',TourController::class);
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
        Route::any('/uploaded-files/file-info', [AizUploadController::class, 'file_info'])->name('uploaded-files.info');
        Route::get('/uploaded-files/destroy/{id}', [AizUploadController::class, 'destroy'])->name('uploaded-files.destroy');

        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity.logs');
    });



    // Uploader
    Route::get('/refresh-csrf', function(){
        return csrf_token();
    });
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
    
    // Route::get('/payment-methods-settings', 'SettingController@payment_method_settings')->name('payment_method_settings');
    // Route::post('/payment_method_update', 'SettingController@payment_method_update')->name('payment_method.update');

    // Route::get('/third-party-settings', 'SettingController@third_party_settings')->name('third_party_settings');
    // Route::post('/third-party-settings/update', 'SettingController@third_party_settings_update')->name('third_party_settings.update');

    // Route::get('/social-media-login-settings', 'SettingController@social_media_login_settings')->name('social_media_login');

    // Route::get('//member-profile-sections', 'SettingController@member_profile_sections_configuration')->name('member_profile_sections_configuration');

    // // website setting
    // Route::group(['prefix' => 'website'], function () {
    //     Route::get('/header_settings', 'SettingController@website_header_settings')->name('website.header_settings');
    //     Route::get('/footer_settings', 'SettingController@website_footer_settings')->name('website.footer_settings');
    //     Route::get('/appearances', 'SettingController@website_appearances')->name('website.appearances');
    //     Route::resource('custom-pages', 'PageController');
    //     Route::get('/custom-pages/edit/{id}', 'PageController@edit')->name('custom-pages.edit');
    //     Route::get('/custom-pages/destroy/{id}', 'PageController@destroy')->name('custom-pages.destroy');
    // });

    // Route::resource('staffs', 'StaffController');
    // Route::get('/staffs/destroy/{id}', 'StaffController@destroy')->name('staffs.destroy');
});
