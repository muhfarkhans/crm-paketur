<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyUser extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $table = "company_users";

    protected $fillable = [
        'user_id',
        'company_id',
    ];

    public function user()
    {
        return $this->hasOne(User::class, "id", "user_id");
    }

    public function company()
    {
        return $this->belongsTo(Company::class, "id", "company_id");
    }
}
