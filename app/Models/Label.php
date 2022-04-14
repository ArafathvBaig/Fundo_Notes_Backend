<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    use HasFactory;

    protected $table = "labels";
    protected $fillable = ['label_name'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function note()
    {
        return $this->belongsTo(Notes::class);
    }
    public function labelnote()
    {
        return $this->belongsTo(LabelNotes::class);
    }
}
