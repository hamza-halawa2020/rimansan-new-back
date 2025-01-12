<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'users';
    protected $fillable = [
        'name',
        'slug',
        'phone',
        'email',
        'image',
        'password',
        'type'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function socialiLinks()
    {
        return $this->hasMany(SocialLink::class);
    }
    public function addSideBanners()
    {
        return $this->hasMany(AddSideBarBanner::class);
    }
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function postCommentsForUser()
    {
        return $this->hasMany(PostComment::class, 'user_id');
    }
    public function postCommentsForAdmin()
    {
        return $this->hasMany(PostComment::class, 'admin_id');
    }

    public function productReviewsForUser()
    {
        return $this->hasMany(ProductReview::class, 'user_id');
    }
    public function productReviewsForAdmin()
    {
        return $this->hasMany(ProductReview::class, 'admin_id');
    }
    public function favourites()
    {
        return $this->hasMany(Favourite::class);
    }
    public function instructors()
    {
        return $this->hasMany(Instructor::class);
    }
    public function courses()
    {
        return $this->hasMany(Course::class);
    }
    public function certifications()
    {
        return $this->hasMany(Certification::class);
    }
    public function courseReviews()
    {
        return $this->hasMany(CourseReview::class);
    }
    public function events()
    {
        return $this->hasMany(Event::class);
    }
    public function faqs()
    {
        return $this->hasMany(Faq::class);
    }
    public function mainSliders()
    {
        return $this->hasMany(MainSlider::class);
    }
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
    public function ordersForUsers()
    {
        return $this->hasMany(Order::class);
    }
    public function ordersForAdmin()
    {
        return $this->hasMany(Order::class);
    }
}
