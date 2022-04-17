<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Loan extends Model
{
    use HasFactory;

    protected static function boot() 
    {
        parent::boot();
        
        static::addGlobalScope('customer', function (Builder $builder) {
            //only for customers, since admin can access all records
            if (Auth::user()->role->role_name == 'customer') {
                $builder->where('customer_id', Auth::id());
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id', 'amount', 'term'
    ];

    /**
     * One Loan to Many scheduled repayments
     */
    public function repayments() {
        return $this->hasMany('App\Models\ScheduledRepayment');
    }

}
