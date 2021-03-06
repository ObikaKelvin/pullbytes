<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Coupon extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $dates = ['deleted_at'];

    public function users()
    {
        return $this->belongsTo(User::class);
    }

    public function plans()
    {
        return $this->belongsTo(Plan::class);
    }

    public function promoCodes()
    {
        return $this->belongsTo(PromoCode::class);
    }
}
