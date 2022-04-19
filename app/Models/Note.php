<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;
    protected $table = 'notes';
    protected $fillable = [
        'title',
        'description'
    ];

    /**
     * Create a new Note with the given attributes
     * for the user id given
     */
    public static function createNote($request, $user_id)
    {
        $note = new Note;
        $note->title = $request->title;
        $note->description = $request->description;
        $note->user_id = $user_id;
        $note->save();

        // $note = Note::create([
        //     'title' => $request->title,
        //     'description' => $request->description,
        //     'user_id' => $user_id
        // ]);
        //return $note;
    }

    /**
     * Function to get Note by Note_Id
     * Passing the Note_id as the parameter
     * 
     * @return array
     */
    public static function getNoteByNoteId($id)
    {
        $notes = Note::where('id', $id)->first();
        return $notes;
    }

    /**
     * Function to get Notes by User_Id
     * Passing the User_id as the parameter
     * 
     * @return array
     */
    public static function getNotesByUserId($id)
    {
        $notes = Note::where('user_id', $id)->get();
        return $notes;
    }

    /**
     * Function to get Notes by Note_Id and User_Id
     * Passing the Note_id and User_id as the parameter
     * 
     * @return array
     */
    public static function getNotesByNoteIdandUserId($id, $user_id)
    {
        $notes = Note::where('id', $id)->where('user_id', $user_id)->first();
        return $notes;
    }

    /**
     * Function to update the note
     * Passing the notes, user_id and credentials to update
     * 
     * @return mixed
     */
    public static function updateNote($notes, $request, $user_id){
        //$notes->id = $request->id;
        $notes->user_id = $user_id;
        $notes->title = $request->title;
        $notes->description = $request->description;
        return $notes->update();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function label()
    {
        return $this->hasMany(Label::class);
    }
    public function labelnote()
    {
        return $this->belongsTo(LabelNotes::class);
    }
}
