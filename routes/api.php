<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AddSideBarBannerController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CertificationController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\CourseReviewController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\FavouriteController;
use App\Http\Controllers\Api\ForgetPasswordController;
use App\Http\Controllers\Api\InstructorController;
use App\Http\Controllers\Api\MainSliderController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymobIntegrationController;
use App\Http\Controllers\Api\PostCommentController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ProductReviewController;
use App\Http\Controllers\Api\ShipmentController;
use App\Http\Controllers\Api\SocialLinkController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VerificationCodeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ContactController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


/**************************** start auth api ****************************/
Route::post('register', [AuthController::class, 'register']);
Route::post('send-verification-code', [VerificationCodeController::class, 'sendVerificationCode']);
Route::post('verify-code', [VerificationCodeController::class, 'verifyCode']);
Route::post('login', [AuthController::class, 'login']);
Route::post('admin-login', [AuthController::class, 'adminLogin']);
Route::post('logout', [AuthController::class, 'logout']);
Route::post('forgot-password', [ForgetPasswordController::class, 'forgotPassword']);
Route::post('password-reset', [ForgetPasswordController::class, 'resetPassword']);
/**************************** end auth api ****************************/

/**************************** start user api ****************************/
Route::apiResource('users', UserController::class);
Route::get('profile', [UserController::class, 'profile']);
Route::post('users/{id}', [UserController::class, 'update']);
/**************************** end user api ****************************/

/**************************** start client api ****************************/
Route::apiResource('clients', ClientController::class);
/**************************** end client api ****************************/

/**************************** start coupon api ****************************/
Route::apiResource('coupons', CouponController::class);
/**************************** end coupon api ****************************/

/**************************** start category api ****************************/
Route::apiResource('categories', CategoryController::class);
/**************************** end category api ****************************/

/**************************** start tag api ****************************/
Route::apiResource('tags', TagController::class);
/**************************** end tag api ****************************/

/**************************** start product api ****************************/
Route::apiResource('products', ProductController::class);
Route::post('products/{id}', [ProductController::class, 'update']);
Route::get('/product/category/{id}', [ProductController::class, 'indexByCategory']);
Route::delete('products/{product}/images/{image}', [ProductController::class, 'deleteImage']);
/**************************** end product api ****************************/

/**************************** start contact us api ****************************/
Route::apiResource('contacts', ContactController::class);
/**************************** end contact us api ****************************/

/**************************** start social-links api ****************************/
Route::apiResource('social-links', SocialLinkController::class);
Route::post('social-links/{id}', [SocialLinkController::class, 'update']);
/**************************** end social-links api ****************************/

/**************************** start side-bar-banner api ****************************/
Route::apiResource('side-bar-banners', AddSideBarBannerController::class);
Route::post('side-bar-banners/{id}', [AddSideBarBannerController::class, 'update']);
Route::get('side-bar-banners-all', [AddSideBarBannerController::class, 'all']);
/**************************** end side-bar-banner api ****************************/

/**************************** start posts api ****************************/
Route::apiResource('posts', PostController::class);
Route::post('posts/{id}', [PostController::class, 'update']);
Route::get('random-posts', [PostController::class, 'randomPosts']);

/**************************** end posts api ****************************/

/**************************** start comments api ****************************/
Route::apiResource('comments', PostCommentController::class);
Route::get('comments-all', [PostCommentController::class, 'all']);
Route::get('comments-show/{id}', [PostCommentController::class, 'showAll']);
Route::put('comments-update/{id}', [PostCommentController::class, 'active']);
/**************************** end comments api ****************************/

/**************************** start product review api ****************************/
Route::apiResource('product-reviews', ProductReviewController::class);
Route::get('product-reviews-all', [ProductReviewController::class, 'all']);
Route::get('product-reviews-show/{id}', [ProductReviewController::class, 'showAll']);
Route::put('product-reviews-update/{id}', [ProductReviewController::class, 'active']);
Route::post('product-reviews-store', [ProductReviewController::class, 'storeByClient']);
/**************************** end product review api ****************************/

/**************************** start favourites api ****************************/
Route::apiResource('favourites', FavouriteController::class);
/**************************** end favourites api ****************************/

/**************************** start instructors api ****************************/
Route::apiResource('instructors', InstructorController::class);
Route::post('instructors/{id}', [InstructorController::class, 'update']);
Route::get('random-instructors', [InstructorController::class, 'randomInstructors']);

/**************************** end instructors api ****************************/

/**************************** start courses api ****************************/
Route::apiResource('courses', CourseController::class);
Route::post('courses/{id}', [CourseController::class, 'update']);
Route::get('random-courses', [CourseController::class, 'randomCourses']);

/**************************** end courses api ****************************/

/**************************** start certifications api ****************************/
Route::apiResource('certifications', CertificationController::class);
Route::post('certifications/{id}', [CertificationController::class, 'update']);

/**************************** end certifications api ****************************/

/**************************** start course review api ****************************/
Route::apiResource('course-reviews', CourseReviewController::class);
Route::get('course-reviews-all', [CourseReviewController::class, 'all']);
Route::get('course-reviews-show/{id}', [CourseReviewController::class, 'showAll']);
Route::put('course-reviews-update/{id}', [CourseReviewController::class, 'active']);
/**************************** end course review api ****************************/

/**************************** start events api ****************************/
Route::apiResource('events', EventController::class);
Route::post('events/{id}', [EventController::class, 'update']);
Route::delete('events/{event}/images/{image}', [EventController::class, 'deleteImage']);

/**************************** end events api ****************************/

/**************************** start faqs api ****************************/
Route::apiResource('faqs', FaqController::class);
/**************************** end faqs api ****************************/

/**************************** start main-sliders api ****************************/
Route::apiResource('main-sliders', MainSliderController::class);
Route::post('main-sliders/{id}', [MainSliderController::class, 'update']);
Route::get('main-sliders-all', [MainSliderController::class, 'all']);
Route::put('main-sliders-update/{id}', [MainSliderController::class, 'active']);
/**************************** end main-sliders api ****************************/

/**************************** start carts api ****************************/
Route::apiResource('carts', CartController::class);
/**************************** end carts api ****************************/

/**************************** start countries api ****************************/
Route::apiResource('countries', CountryController::class);
/**************************** end countries api ****************************/

/**************************** start cities api ****************************/
Route::apiResource('cities', CityController::class);
Route::get('cities/countries/{countryId}', [CityController::class, 'getCitiesByCountry']);
/**************************** end cities api ****************************/

/**************************** start addresses api ****************************/
Route::apiResource('addresses', AddressController::class);
Route::get('addresses-admin', [AddressController::class, 'adminIndex']);
/**************************** end addresses api ****************************/

/**************************** start shipments api ****************************/
Route::apiResource('shipments', ShipmentController::class);
/**************************** end shipments api ****************************/

/**************************** start orders api ****************************/
Route::apiResource('orders', OrderController::class);
Route::get('orders-track/{orderNumber}', [OrderController::class, 'trackOrder']);
Route::get('orders-my-orders', [OrderController::class, 'myOrders']);
Route::get('orders-filter/{status}', [OrderController::class, 'filterByStatus']);
Route::post('orders-change/{id}', [OrderController::class, 'changeStatus']);
Route::post('orders-store', [OrderController::class, 'storeByClient']);
Route::get('orders-export', [OrderController::class, 'exportPendingOrders']);

/**************************** end orders api ****************************/

/**************************** start paymob integration api ****************************/
Route::get('payments', [PaymobIntegrationController::class, 'index']);
Route::get('state', [PaymobIntegrationController::class, 'state']);
Route::prefix('payment')->group(function () {
    Route::post('/credit', [PaymobIntegrationController::class, 'credit']);
    Route::post('/callback', [PaymobIntegrationController::class, 'callback']);
});
/**************************** end paymob integration api ****************************/
