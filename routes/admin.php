<?php

use App\Exports\ToursSampleExport;
use App\Http\Controllers\API\OrderController as APIOrderController;
use App\Http\Controllers\API\TourController as APITourController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AddonController;
use App\Http\Controllers\AizUploadController;
use App\Http\Controllers\BusinessExpenseController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\ExclusionController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\InclusionController;
use App\Http\Controllers\ItineraryController;
use App\Http\Controllers\ManifestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PickupController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SmsTemplateController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\SubCateoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TaxesFeeController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\TourTypeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\TourGalleryController;


Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {

    Route::get('/tour/{slug}/fetch_one', [APITourController::class, 'fetch_one'])->name('tour.fetch_one');
    Route::get('/tour-sessions', [APIOrderController::class, 'getSessionTimes'])->name('tour.sessions');
    Route::get('/tour/{slug}/booking', [APITourController::class, 'fetch_booking'])->name('tour.fetch_booking');

    Route::get('/dashboard',[ProfileController::class,'dashboard'])->name('dashboard');
    Route::get('/reports/tour-wise',[DashboardController::class,'dashboard'])->name('report.tour-wise');
    Route::get('/reports/comparison', [DashboardController::class, 'comparisonView'])->name('report.comparison');
    Route::get('/reports/comparison-data', [DashboardController::class, 'comparisonData'])->name('report.comparison.data');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/suplier_update', [ProfileController::class, 'suplierUpdate'])->name('profile.suplier_update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/convert-currency', [CurrencyController::class, 'convert'])->name('currency.convert');
    
    Route::post('/set-currency', function (\Illuminate\Http\Request $request) {

        $currency = $request->currency;

        if (empty($currency)) {
            session()->forget('currency'); // back to default
        } else {
            session(['currency' => strtoupper($currency)]);
        }

        return response()->noContent();
    })->name('set.currency');

    Route::resource('/user',UserController::class);
    Route::get('/user_supplier',[SupplierController::class, 'index'])->name('supplier.index');
    Route::get('/user_driver',[DriverController::class, 'index'])->name('driver.index');
    Route::resource('/customers',CustomerController::class);
    Route::resource('/vehicles',VehicleController::class);

    Route::get('/customers/{id}/{source}/edit',[CustomerController::class, 'editFromSource'])->name('customers.edit.source');
    Route::put(
    '/customers-source/{id}/{source}',
    [CustomerController::class, 'updateSource']
)->name('customers.source.update');

    Route::post('/admin/customer/update_details', [CustomerController::class, 'updateOrderCustomerDetails'])->name('customer.update_details');
    
    Route::resource('/role',RoleController::class);
    Route::resource('/permission',PermissionController::class);
    Route::resource('/category',CategoryController::class);

    Route::post('/category/{id}/clone', [CategoryController::class, 'clone'])
    ->name('category.clone');
    
    Route::resource('/tour_type',TourTypeController::class);
    Route::resource('/collection',CollectionController::class);
    
    Route::resource('/orders', OrderController::class);
    Route::get('/order/rezdy-manifest', [OrderController::class, 'showPdfFiles']);
    Route::post('/orders/{id}/update-status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::get('/order-manifest', [OrderController::class, 'manifest'])->name('orders.manifest');
    Route::post('/internal-order/store', [OrderController::class, 'internalOrderStore'])->name('orders.internal.store');
    Route::get('/ordersmanifest/download', [OrderController::class, 'downloadManifest'])->name('orders.manifest.download');

    Route::get('/tour-manifest', [OrderController::class, 'tourManifest'])->name('orders.tour.manifest');
    Route::get('toursmanifest/download', [OrderController::class, 'downloadTourManifest'])->name('orders.tour.manifest.download');

    Route::delete('/order/destroy/{id}', [OrderController::class, 'destroy'])->name('order.destroy');

    
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

    Route::post('/cities/update-order', [CityController::class, 'updateOrder'])->name('cities.updateOrder');

    // Addone
    Route::resource('addon',AddonController::class);
    Route::get('/addon/destroy/{id}', [AddonController::class, 'destroy'])->name('addon.destroy');
    Route::post('/addon/sort-order', [AddonController::class, 'updateOrder'])->name('addon.order');

    // Pickup
    Route::resource('pickups',PickupController::class);
    Route::get('/pickups/destroy/{id}', [PickupController::class, 'destroy'])->name('pickup.destroy');
    Route::post('/pickups/sort-order', [PickupController::class, 'updateOrder'])->name('pickup.order');
    Route::post('/order/pickups/update', [PickupController::class, 'orderPickupUpdate'])->name('order.pickup.update');

    

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
    Route::get('/tour/{id}/edit/partner', [TourController::class, 'editPartner'])->name('tour.edit.partner');
    Route::get('/tour/{id}/edit/info_seo', [TourController::class, 'editinfoSeo'])->name('tour.edit.infoseo');
    Route::get('/tour/{id}/edit/seoscore', [TourController::class, 'editSeoScore'])->name('tour.edit.seoscore');
    Route::get('/tour/{id}/edit/notification', [TourController::class, 'editNotification'])->name('tour.edit.message.notification');
    Route::get('/tour/{id}/edit/reminder', [TourController::class, 'editReminder'])->name('tour.edit.message.reminder');
    Route::get('/tour/{id}/edit/followup', [TourController::class, 'editFollowup'])->name('tour.edit.message.followup');
    Route::get('/tour/{id}/edit/paymentrequest', [TourController::class, 'editPaymentRequest'])->name('tour.edit.message.paymentrequest');
    Route::get('/admin/city-search', [TourController::class, 'citySearch'])->name('city.search');
    Route::get('/admin/category-search', [TourController::class, 'categorySearch'])->name('category.search');
    Route::get('/tour/{id}/edit/specialdeposit', [TourController::class, 'specialdeposit'])->name('tour.edit.special.deposit');
    Route::get('/tour/{id}/edit/review', [TourController::class, 'review'])->name('tour.edit.review');
    Route::get('/tour/{id}/edit/schedule-calendar', [TourController::class, 'scheduleCalendar'])->name('tour.edit.schedule-calendar');
    Route::get('/tour/{id}/edit/shedule-pricing', [TourController::class, 'schedulePricing'])->name('tour.edit.schedule-pricing');
    Route::get('/tour/{id}/edit/schedule-calendar-event', [TourController::class, 'scheduleCalendarEvent'])->name('tour.edit.schedule-calendar-event');
    Route::get('/admin/city-search', [TourController::class, 'citySearch'])->name('city.search');
    Route::get('/admin/category-search', [TourController::class, 'categorySearch'])->name('category.search');
    Route::post('/schedule-delete-slots', [TourController::class, 'storeDeleteSlot'])->name('tour.delete-slots.store');
    Route::post('/schedule-delete-slots', [TourController::class, 'storeDeleteSlot'])->name('tour.delete-slots.store');
    Route::get('/export-tours', [TourController::class, 'exportTours'])->name('tours.export');
    
    Route::post('/tours/mark-review', [TourController::class, 'markReview'])
    ->name('tours.markReview');

    Route::get('/tour/{id}/edit/parent-tour', [TourController::class, 'parentTour'])->name('tour.edit.parent');
    





    Route::get('/download-sample-excel', function () {
        return Excel::download(new ToursSampleExport, 'tours_sample.xlsx');
    })->name('tours.sample.download');


    // Tour Preview
    Route::get('/tour/clone/{id}', [TourController::class, 'clone'])->name('tour.clone');
    Route::get('/tour/destroy/{id}', [TourController::class, 'destroy'])->name('tour.destroy');
    Route::post('/tour/basic_detail_update/{id}', [TourController::class, 'basic_detail_update'])->name('tour.basic_detail_update');
    Route::post('/tour/addon_update/{id}', [TourController::class, 'addon_update'])->name('tour.addon_update');
    Route::post('/tour/location_update/{id}', [TourController::class, 'location_update'])->name('tour.location_update');
    Route::put('/tour/pickup_update/{id}', [TourController::class, 'pickup_update'])->name('tour.pickup_update');
    Route::put('/tour/seo_update/{id}', [TourController::class, 'seo_update'])->name('tour.seo_update');
    Route::post('/tour/booking_update/{id}', [TourController::class, 'booking_update'])->name('tour.booking_update');
    Route::post('/tour/partner_update/{id}', [TourController::class, 'partner_update'])->name('tour.partner_update');
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
    Route::post('/tours/update-price', [TourController::class, 'updatePrices'])->name('tour.updatePrices');
    Route::delete('/tours/tour-bulkDelete', [TourController::class, 'bulkDelete'])->name('tour.bulkDelete');
    Route::post('/tours/toggle-status', [TourController::class, 'toggleStatus'])->name('tour.toggleStatus');
    Route::post('/tours/import-price', [TourController::class, 'importPrice'])->name('tours.importPrice');

    // Route::post('/tour/{id}/edit/specialdeposit', [TourController::class, 'specialdeposit'])->name('tour.edit..special.deposit');
    Route::put('/tour/special-deposit/{id}', [TourController::class, 'specialDepositUpdate'])->name('tour.special-deposit');
    Route::post('/tour/shedule-pricing/{id}', [TourController::class, 'schedulePricingUpdate'])->name('tour.shedule-pricing');

    Route::put('/tour/review/{id}', [TourController::class, 'reviewUpdate'])->name('tour.review');

    Route::put('/tour/parent-tour/{id}', [TourController::class, 'parentUpdate'])
    ->name('tour.parent');
    Route::get('/tours/{id}/sub-create', [TourController::class, 'createSubTour'])->name('tours.sub-create');
    Route::post('/tours/{id}/sub-tour-store', [TourController::class, 'subTourStore'])->name('tour.sub-tour-store');
    Route::get('/tours/{id}/sub-edit', [TourController::class, 'editSubTour'])->name('tour.sub-tour.edit');
    Route::get('/tours/{id}/sub-index', [TourController::class, 'subTourIndex'])->name('tour.sub-tour.index');
    Route::get('/tours/tours-list', [TourController::class, 'toursList'])->name('tours.tours-list');

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
    Route::get('/activity-descriptive', [ActivityLogController::class, 'descriptive'])->name('activity.descriptive');
    Route::get('/order-logs', [ActivityLogController::class, 'orderLog'])->name('activity.orderLog');
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
    Route::post('/orders/{order}/captureInitialPayment', [OrderController::class, 'captureInitialPayment'])->name('orders.captureInitialPayment');
    Route::post('/orders/{order}/cancelInitialPayment', [OrderController::class, 'cancelInitialPayment'])->name('orders.cancelInitialPayment');


    

    Route::post('/orders/{order}/payment-details', [OrderController::class, 'getPaymentDetails'])->name('orders.payment-details');
    Route::post('orders/{order}/refund', [OrderController::class, 'refundPayment'])
    ->name('orders.refundPayment');
    Route::post('/admin/orders/{order}/refund-multiple', [OrderController::class, 'refundMultiple'])->name('orders.refundMultiple');
    Route::post('orders/{order}/refund2322', [OrderController::class, 'refundPayment'])->name('orders.refund');

    Route::post('/orders/{order}/remove-card', [OrderController::class, 'removeCard'])
    ->name('orders.remove-card');
    Route::post('/orders/{order}/add-card', [OrderController::class, 'addCard'])
    ->name('orders.add-card');


    Route::post('/admin/orders/{order}/add-payment', [OrderController::class, 'addStripePayment'])
    ->name('orders.addPayment');

    Route::post('/admin/orders/order_tour/delete', [OrderController::class, 'removeOrderTour'])
    ->name('order_tour.delete');


    // SMS Templates
    Route::resource('/sms-templates', SmsTemplateController::class);
    Route::post('/sms-templates/update', [SmsTemplateController::class, 'update'])->name('sms-templates.update');
    Route::post('/sms-templates/preview/{id}', [SmsTemplateController::class, 'preview'])->name('sms-templates.preview');
    
    // Email Templates
    Route::resource('/email-templates', EmailTemplateController::class);
    Route::post('/email-templates/update', [EmailTemplateController::class, 'update'])->name('email-templates.update');
    Route::get('/email-templates/preview/{id}', [EmailTemplateController::class, 'preview'])->name('email-templates.preview');

    
    Route::get('/clear-cache', function() {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('permission:cache-reset');
        return redirect()->back()->with('success', 'Cache cleared!');
    })->name('clear.cache');

    Route::get('/optimize-cache', function () {
        Artisan::call('optimize:clear');
        return back()->with('success','Cache cleared');
    })->name('optimize.cache');

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

    Route::resource('promos', PromoController::class);
    Route::resource('vouchers', VoucherController::class);
    Route::post('/apply-promo', [PromoController::class, 'apply'])->name('promo.apply');

    Route::post('/tour/single', [\App\Http\Controllers\API\TourController::class,'single'])->name('tour.single');
    Route::post('/tour/calendar', [\App\Http\Controllers\API\TourController::class,'singleCalendar'])->name('tour.calendar');


    Route::get('/admin/orders/sample-excel', [OrderController::class, 'sampleExcel'])
    ->name('orders.sample-excel');

    Route::post('/admin/orders/import-orders', [OrderController::class, 'importOrders'])
    ->name('orders.import');

    Route::resource('partners', PartnerController::class);
    Route::get('report/overview', [ReportController::class, 'overview'])->name('report.overview');
    Route::get('report/revenue', [ReportController::class, 'revenue'])->name('report.revenue');
    Route::get('reports/revenue/export', [ReportController::class, 'exportRevenue'])
    ->name('report.revenue.export');

    Route::get('report/invoice', [ReportController::class, 'invoice'])->name('report.invoice');

        // Invoice Excel Export
    Route::get('report/invoice/export', [ReportController::class, 'invoiceExport'])->name('report.invoice.export');

    Route::get('/reports/invoice-details', [ReportController::class, 'invoiceWithDetails'])
    ->name('report.invoice.details');

    Route::get('/reports/invoice-details/export', [ReportController::class, 'invoiceWithDetailsExport'])
    ->name('report.invoice.details.export');

    Route::get('reports/customer/export', [ReportController::class, 'exportCustomer'])
    ->name('report.customer.export');

    Route::get('report/schedule-pricing-report', [ReportController::class, 'schedulePricingReport'])->name('report.schedule-pricing-report');
    Route::get('/schedule-pricing-export', [ReportController::class, 'schedulePricingExport'])
    ->name('report.schedule.export');
    Route::get('report/price_schedule', [ReportController::class, 'reportPriceSchedule'])->name('report.price_schedule');
    Route::get('/reports/price-schedule/export', [ReportController::class, 'exportPriceSchedule'])
    ->name('report.price_schedule.export');    


    Route::get('/driver-manifest', [ManifestController::class, 'driverManifest'])->name('driver.manifest');
    Route::get('/vehicle-manifest', [ManifestController::class, 'vehicleManifest'])->name('vehicle.manifest');
    Route::get('/vehicle-manifest/export', [ManifestController::class, 'exportVehicleManifest'])->name('vehicle.manifest.export');


    Route::get('/driver-manifest/export', [ManifestController::class, 'exportDriverManifest'])->name('driver.manifest.export');
    Route::post('/passenger-pickup-mail', [ManifestController::class, 'passengerPickupMail'])->name('passenger.pickup.mail');
    Route::post('/driver-pickup-mail', [ManifestController::class, 'driverPickupMail'])->name('driver.pickup.mail');
    Route::post('/assign-driver', [ManifestController::class, 'assignDriver'])->name('assign.driver');
    Route::post('/remove-driver', [ManifestController::class, 'removeDriver'])->name('remove.driver');
    Route::post('/tour-itinerary',[ManifestController::class, 'getTourItinerary'])->name('tour.itinerary');

    Route::resource('business-expenses', BusinessExpenseController::class);

});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {

    Route::get(
        '/tour-gallery',
        [TourGalleryController::class, 'index']
    )->name('tour-gallery.index');

    Route::post(
        '/tour-gallery/{galleryUpload}/approve',
        [TourGalleryController::class, 'approve']
    )->name('tour-gallery.approve');

    Route::post(
        '/tour-gallery/{galleryUpload}/reject',
        [TourGalleryController::class, 'reject']
    )->name('tour-gallery.reject');

    Route::delete(
        '/tour-gallery/{galleryUpload}',
        [TourGalleryController::class, 'destroy']
    )->name('tour-gallery.destroy');

});

Route::get(
    '/tour-gallery/{order}/upload',
    [TourGalleryController::class, 'show']
)
    ->name('tour-gallery.show')
    ->middleware('signed');

Route::post(
    '/tour-gallery/{order}/upload',
    [TourGalleryController::class, 'store']
)
    ->name('tour-gallery.store')
    ->middleware('signed');
