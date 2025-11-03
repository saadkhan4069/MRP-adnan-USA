<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wproduction extends Model
{
     protected $table = 'wproductions';
    protected $fillable =[

        	"id","name","email","phone","company","address","is_active","created_at","updated_at"	
    ];



}
