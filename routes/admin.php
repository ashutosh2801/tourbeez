<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AddonController;
use App\Http\Controllers\ContactController;
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
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PickupController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SmsTemplateController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\SubCateoryController;
use App\Http\Controllers\TaxesFeeController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\TourTypeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard',[ProfileController::class,'dashboard'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/suplier_update', [ProfileController::class, 'suplierUpdate'])->name('profile.suplier_update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('/user',UserController::class);
    Route::resource('/customers',CustomerController::class);
    Route::resource('/role',RoleController::class);
    Route::resource('/permission',PermissionController::class);
    Route::resource('/category',CategoryController::class);
    Route::resource('/tour_type',TourTypeController::class);
    Route::resource('/collection',CollectionController::class);
    
    Route::resource('/orders', OrderController::class);
    Route::get('/order/rezdy-manifest', [OrderController::class, 'showPdfFiles']);
    Route::post('/orders/{id}/update-status', [OrderController::class, 'updateStatus']);
    Route::get('/order-manifest', [OrderController::class, 'manifest'])->name('orders.manifest');
    Route::post('/internal-order/store', [OrderController::class, 'internalOrderStore'])->name('orders.internal.store');
    Route::get('/ordersmanifest/download', [OrderController::class, 'downloadManifest'])->name('orders.manifest.download');

    Route::get('/tour-manifest', [OrderController::class, 'tourManifest'])->name('orders.tour.manifest');
    Route::get('toursmanifest/download', [OrderController::class, 'downloadTourManifest'])->name('orders.tour.manifest.download');
    
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
    Route::get('/tour/{id}/edit/optionals', [TourController::class, 'editOptionals'])->name('tour.edit.optionals');
    Route::get('/tour/{id}/edit/exclusions', [TourController::class, 'editExclusions'])->name('tour.edit.exclusions');
    Route::get('/tour/{id}/edit/taxesfees', [TourController::class, 'editTaxesfees'])->name('tour.edit.taxesfees');
    Route::get('/tour/{id}/edit/gallery', [TourController::class, 'editGallery'])->name('tour.edit.gallery');
    Route::get('/tour/{id}/edit/seo', [TourController::class, 'editSeo'])->name('tour.edit.seo');
    Route::get('/tour/{id}/edit/booking', [TourController::class, 'editBooking'])->name('tour.edit.booking');
    Route::get('/tour/{id}/edit/info_seo', [TourController::class, 'editinfoSeo'])->name('tour.edit.infoseo');
    Route::get('/tour/{id}/edit/seoscore', [TourController::class, 'editSeoScore'])->name('tour.edit.seoscore');
    Route::get('/tour/{id}/edit/notification', [TourController::class, 'editNotification'])->name('tour.edit.message.notification');
    Route::get('/tour/{id}/edit/reminder', [TourController::class, 'editReminder'])->name('tour.edit.message.reminder');
    Route::get('/tour/{id}/edit/followup', [TourController::class, 'editFollowup'])->name('tour.edit.message.followup');
    Route::get('/tour/{id}/edit/paymentrequest', [TourController::class, 'editPaymentRequest'])->name('tour.edit.message.paymentrequest');
    Route::get('/admin/city-search', [TourController::class, 'citySearch'])->name('city.search');
    Route::get('/tour/{id}/edit/specialdeposit', [TourController::class, 'specialdeposit'])->name('tour.edit.special.deposit');
    Route::get('/tour/{id}/edit/review', [TourController::class, 'review'])->name('tour.edit.review');
    Route::get('/tour/{id}/edit/schedule-calendar', [TourController::class, 'scheduleCalendar'])->name('tour.edit.schedule-calendar');
    Route::get('/tour/{id}/edit/schedule-calendar-event', [TourController::class, 'scheduleCalendarEvent'])->name('tour.edit.schedule-calendar-event');
    Route::post('/schedule-delete-slots', [TourController::class, 'storeDeleteSlot'])->name('tour.delete-slots.store');

    // Tour Preview
    Route::get('/tour/clone/{id}', [TourController::class, 'clone'])->name('tour.clone');
    Route::get('/tour/destroy/{id}', [TourController::class, 'destroy'])->name('tour.destroy');
    Route::post('/tour/basic_detail_update/{id}', [TourController::class, 'basic_detail_update'])->name('tour.basic_detail_update');
    Route::post('/tour/addon_update/{id}', [TourController::class, 'addon_update'])->name('tour.addon_update');
    Route::post('/tour/location_update/{id}', [TourController::class, 'location_update'])->name('tour.location_update');
    Route::put('/tour/pickup_update/{id}', [TourController::class, 'pickup_update'])->name('tour.pickup_update');
    Route::put('/tour/seo_update/{id}', [TourController::class, 'seo_update'])->name('tour.seo_update');
    Route::post('/tour/booking_update/{id}', [TourController::class, 'booking_update'])->name('tour.booking_update');
    Route::put('/tour/schedule_update/{id}', [TourController::class, 'schedule_update'])->name('tour.schedule_update');
    Route::put('/tour/itinerary_update/{id}', [TourController::class, 'itinerary_update'])->name('tour.itinerary_update');
    Route::put('/tour/faq_update/{id}', [TourController::class, 'faq_update'])->name('tour.faq_update');
    Route::put('/tour/inclusion_update/{id}', [TourController::class, 'inclusion_update'])->name('tour.inclusion_update');
    Route::put('/tour/optional_update/{id}', [TourController::class, 'optional_update'])->name('tour.optional_update');
    Route::put('/tour/exclusion_update/{id}', [TourController::class, 'exclusion_update'])->name('tour.exclusion_update');
    Route::put('/tour/taxfee_update/{id}', [TourController::class, 'taxfee_update'])->name('tour.taxfee_update');
    Route::put('/tour/gallery_update/{id}', [TourController::class, 'gallery_update'])->name('tour.gallery_update');
    Route::put('/tour/notification_update/{id}', [TourController::class, 'notification_update'])->name('tour.notification_update');
    Route::put('/tour/reminders_update/{id}', [TourController::class, 'reminders_update'])->name('tour.reminders_update');
    Route::put('/tour/followup_update/{id}', [TourController::class, 'followup_update'])->name('tour.followup_update');
    Route::put('/tour/payment_request_update/{id}', [TourController::class, 'payment_request_update'])->name('tour.payment_request_update');
    Route::get('/tour/preview/{id}', [TourController::class, 'preview'])->name('tour.preview');
    Route::post('/tour/addfocus/{id}', [TourController::class, 'add_focus_keyword'])->name('tour.addfocus');
    Route::post('/tours/reorder', [TourController::class, 'reorder'])->name('tour.reorder');
    Route::post('/tours/save-coupon', [TourController::class, 'saveCoupon'])->name('tour.saveCoupon');
    Route::delete('/tours/tour-bulkDelete', [TourController::class, 'bulkDelete'])->name('tour.bulkDelete');
    Route::post('/tours/toggle-status', [TourController::class, 'toggleStatus'])->name('tour.toggleStatus');
    // Route::post('/tour/{id}/edit/specialdeposit', [TourController::class, 'specialdeposit'])->name('tour.edit..special.deposit');
    Route::put('/tour/special-deposit/{id}', [TourController::class, 'specialDepositUpdate'])->name('tour.special-deposit');
    Route::put('/tour/review/{id}', [TourController::class, 'reviewUpdate'])->name('tour.review');
    Route::get('/tours/{id}/sub-create', [TourController::class, 'createSubTour'])->name('tours.sub-create');
    Route::post('/tours/{id}/sub-tour-store', [TourController::class, 'subTourStore'])->name('tour.sub-tour-store');
    Route::get('/tours/{id}/sub-edit', [TourController::class, 'editSubTour'])->name('tour.sub-tour.edit');
    Route::get('/tours/{id}/sub-index', [TourController::class, 'subTourIndex'])->name('tour.sub-tour.index');

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

    Route::resource('optionals',InclusionController::class);
    Route::post('/optionals/single', [InclusionController::class, 'single'])->name('optionals.single');

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
    Route::get('/banner', [AizUploadController::class, 'showBanner'])->name('banner.index');
  
    Route::get('banners/create', [AizUploadController::class, 'bannerCreate'])->name('banners.create');
    Route::post('banners/store', [AizUploadController::class, 'bannerStoreOrUpdate'])->name('banners.store');
    Route::get('banners/{id}/edit', [AizUploadController::class, 'bannerEdit'])->name('banners.edit');
    Route::post('banners/{id}/update', [AizUploadController::class, 'bannerStoreOrUpdate'])->name('banners.update');
    Route::get('banners/{id}/delete', [AizUploadController::class, 'bannerDestroy'])->name('banners.destroy');

    // Uploader
    Route::get('/refresh-csrf', function(){ return csrf_token(); });
    Route::post('/aiz-uploader', [AizUploadController::class,'show_uploader']);
    Route::post('/aiz-uploader/upload', [AizUploadController::class,'upload']);
    Route::get('/aiz-uploader/get_uploaded_files', [AizUploadController::class,'get_uploaded_files']);
    Route::delete('/aiz-uploader/destroy/{id}', [AizUploadController::class,'destroy']);
    Route::post('/aiz-uploader/get_file_by_ids', [AizUploadController::class,'get_preview_files']);
    Route::get('/aiz-uploader/download/{id}', [AizUploadController::class,'attachment_download'])->name('download_attachment');
    Route::get('/migrate/database', [AizUploadController::class,'migrate_database']);

    Route::post('/aiz-uploader/youtube', [AizUploadController::class, 'storeYoutube'])->name('aiz-uploader.youtube');

    // Setting
    Route::resource('/settings', SettingController::class);
    Route::post('/settings/update', [SettingController::class, 'update'])->name('settings.update');
    Route::get('/general-settings', [SettingController::class, 'general_settings'])->name('general_settings');
    Route::get('/email-settings', [SettingController::class, 'email_settings'])->name('email_settings');
    Route::post('/settings/activation/update', [SettingController::class, 'updateActivationSettings'])->name('settings.activation.update');
    Route::get('/payment-methods-settings', [SettingController::class, 'payment_method_settings'])->name('payment_method_settings');
    Route::post('/payment_method_update', [SettingController::class, 'payment_method_update'])->name('payment_method.update');
    Route::get('/third-party-settings', [SettingController::class, 'third_party_settings'])->name('third_party_settings');
    Route::post('/third-party-settings/update', [SettingController::class, 'third_party_settings_update'])->name('third_party_settings.update');
    Route::put('/global/special-deposit', [SettingController::class, 'specialDepositGlobal'])->name('global.special-deposit');
    // Route::post('/global/special-deposit', [SettingController::class, 'specialDepositGlobal'])->name('global.special-deposit');
    // env Update
    Route::post('/env_key_update', [SettingController::class, 'env_key_update'])->name('env_key_update.update');
    Route::post('/settings/test/mail', [SettingController::class, 'testEmail'])->name('test.mail');
    Route::post('/settings/test/send', [SettingController::class, 'testSend'])->name('third_party_settings.send');

    Route::post('/order', [OrderController::class, 'index'])->name('order.index');
    Route::post('/order/order_mail_send/', [OrderController::class, 'order_mail_send'])->name('mail_send');
    Route::post('/order/order_template_details/', [OrderController::class, 'order_template_details'])->name('order_template_details');
    Route::post('/order/order_confirmation_message/', [OrderController::class, 'order_confirmation_message'])->name('order_confirmation_message');
    Route::post('/order/order_sms_send/', [OrderController::class, 'order_sms_send'])->name('order_sms_send');
    Route::delete('/order/bulk-delete', [OrderController::class, 'bulkDelete'])->name('order.bulkDelete');
    Route::post('/orders/{order}/charge', [OrderController::class, 'capturePayment'])->name('orders.charge');
    Route::post('/orders/{order}/payment-details', [OrderController::class, 'getPaymentDetails'])->name('orders.payment-details');

    // SMS Templates
    Route::resource('/sms-templates', SmsTemplateController::class);
    Route::post('/sms-templates/update', [SmsTemplateController::class, 'update'])->name('sms-templates.update');
    Route::post('/sms-templates/preview/{id}', [SmsTemplateController::class, 'preview'])->name('sms-templates.preview');
    
    // Email Templates
    Route::resource('/email-templates', EmailTemplateController::class);
    Route::post('/email-templates/update', [EmailTemplateController::class, 'update'])->name('email-templates.update');
    Route::get('/email-templates/preview/{id}', [EmailTemplateController::class, 'preview'])->name('email-templates.preview');


    Route::get('/tour/{slug}/fetch_one', [\App\Http\Controllers\API\TourController::class, 'fetch_one']);
    Route::get('/tour-sessions', [\App\Http\Controllers\API\OrderController::class, 'getSessionTimes']);
    Route::get('/tour/{slug}/booking', [\App\Http\Controllers\API\TourController::class, 'fetch_booking']);

    
    Route::get('/clear-cache', function() {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('permission:cache-reset');
        return redirect()->back()->with('success', 'Cache cleared!');
    })->name('clear.cache');

    Route::get('/uploaded-disable-date', function() {
        Artisan::call('app:update-tour-disable-date');
        
        return redirect()->back()->with('success', 'Update disabled tour schedule meta for all tours');
    })->name('uploaded-disable-date');

    Route::get('/notifications/navbar', [NotificationController::class, 'navbar'])
    ->name('notifications.navbar');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    Route::get('/notifications/fetch-all', [NotificationController::class, 'fetchAll'])->name('notifications.fetchAll');
// Route::get('/notifications/read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.read');   
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');

    Route::resource('contacts', ContactController::class)->only(['index', 'show', 'destroy']);

});
