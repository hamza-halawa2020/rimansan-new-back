<?php

namespace Database\Seeders;

use App\Models\AddSideBarBanner;
use App\Models\Certification;
use App\Models\City;
use App\Models\Client;
use App\Models\ContactUs;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\CourseReview;
use App\Models\Event;
use App\Models\EventImage;
use App\Models\Faq;
use App\Models\Favourite;
use App\Models\Instructor;
use App\Models\MainSlider;
use App\Models\OrderItem;
use App\Models\Post;
use App\Models\ProductImage;
use App\Models\ProductReview;
use App\Models\SocialLink;
use App\Models\Tag;
use \App\Models\User;
use \App\Models\Product;
use \App\Models\Category;
use \App\Models\Order;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        AddSideBarBanner::factory(2)->create();
        Category::factory(2)->create();
        Certification::factory(2)->create();
        Client::factory(2)->create();
        ContactUs::factory(2)->create();
        Coupon::factory(2)->create();
        Course::factory(2)->create();
        CourseReview::factory(2)->create();
        Event::factory(2)->create();
        EventImage::factory(2)->create();
        Faq::factory(2)->create();
        Favourite::factory(2)->create();
        Instructor::factory(2)->create();
        MainSlider::factory(2)->create();
        // Order::factory(2)->create();
        // OrderItem::factory(2)->create();
        Post::factory(2)->create();
        Product::factory(2)->create();
        ProductImage::factory(2)->create();
        ProductReview::factory(2)->create();
        SocialLink::factory(2)->create();
        Tag::factory(2)->create();
        Country::factory(10)->create();
        City::factory(30)->create();
        User::factory(2)->create();
        User::factory()->create([
            'name' => 'hamza',
            'slug' => 'hamza',
            'phone' => '01149447078',
            'email' => 'hamza@hamza.com',
            'image' => 'default.png',
            'email_verified_at' => now(),
            'password' => bcrypt('12345678'),
            'type' => 'admin',
        ]);
    }
}
