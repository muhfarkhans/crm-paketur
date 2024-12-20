<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $table = "companies";

    protected $fillable = [
        'name',
        'email',
        'phone',
    ];

    public function users()
    {
        return $this->hasMany(CompanyUser::class, "company_id", "id");
    }
}
