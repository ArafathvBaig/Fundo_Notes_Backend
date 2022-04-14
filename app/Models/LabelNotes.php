<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabelNotes extends Model
{
    use HasFactory;

    protected $table = "label_notes";
    protected $fillable = [
        'label_id',
        'user_id',
        'note_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function label()
    {
        return $this->belongsTo(Label::class);
    }

    public function note()
    {
        return $this->belongsTo(Notes::class);
    }
}
