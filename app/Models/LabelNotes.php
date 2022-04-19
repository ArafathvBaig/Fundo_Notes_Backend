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

    /**
     * Function to get LabelNotes by label_id, note_id and user_id
     * Passing label_id, note_id and user_id as parameters
     * 
     * @return array
     */
    public static function getLabelNotesbyLabelIdNoteIdandUserId($request, $user_id){
        $labelnote = LabelNotes::where('note_id', $request->note_id)->where('label_id', $request->label_id)->where('user_id', $user_id)->first();
        return $labelnote;
    }

    /**
     * Function to create a NoteLabel
     * Passing the credentials and user_id as parameters
     */
    public static function createNoteLabel($request, $user_id)
    {
        $labelnotes = LabelNotes::create([
            'user_id' => $user_id,
            'note_id' => $request->note_id,
            'label_id' => $request->label_id
        ]);
    }

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
