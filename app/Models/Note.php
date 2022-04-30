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
        'description',
        'pin',
        'archive',
        'colour',
        'label'
    ];

    /**
     * Create a new Note with the given attributes
     * for the user id given
     * 
     * @return integer
     */
    public static function createNote($request, $user_id)
    {
        $note = new Note;
        $note->title = $request->title;
        $note->description = $request->description;
        $note->user_id = $user_id;
        $note->save();
        return $note->id;

        // $note = Note::create([
        //     'title' => $request->title,
        //     'description' => $request->description,
        //     'user_id' => $user_id
        // ]);
        //return $note->id;
    }

    /**
     * Create a new Note with the given attributes
     * for the user id given
     * 
     * @return integer
     */
    public static function createNotes($request, $user_id, $colour)
    {
        $note = new Note;
        $note->title = $request->title;
        $note->description = $request->description;
        $note->user_id = $user_id;
        if ($request->pin == "1") {
            $note->pin = 1;
        }
        if ($request->archive == "1") {
            $note->archive = 1;
        }
        $note->colour = $colour;
        $note->save();
        return $note;
    }

    /**
     * Function to update the note
     * Passing the notes, user_id and credentials to update
     * 
     * @return mixed
     */
    public static function updateNote($notes, $request, $user_id, $colour)
    {
        //$notes->id = $request->id;
        $notes->user_id = $user_id;
        $notes->title = $request->title;
        $notes->description = $request->description;
        if ($request->pin == "1") {
            $notes->pin = 1;
        } else {
            $notes->pin = 0;
        }
        if ($request->archive == "1") {
            $notes->archive = 1;
        } else {
            $notes->archive = 0;
        }
        $notes->colour = $colour;
        return $notes->update();
    }

    /**
     * Function to update the note of Collaborator
     * Passing the notes and credentials to update
     * 
     * @return mixed
     */
    public static function updateCollaboratorNote($notes, $request)
    {
        $notes->title = $request->title;
        $notes->description = $request->description;
        return $notes->update();
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
     * Function to get the notes and their labels
     * Passing the user as a parameter
     * 
     * @return array
     */
    public static function getNotesandItsLabels($user)
    {
        $notes = Note::leftJoin('collaborators', 'collaborators.note_id', '=', 'notes.id')
            ->leftJoin('label_notes', 'label_notes.note_id', '=', 'notes.id')
            ->leftJoin('labels', 'labels.id', '=', 'label_notes.label_id')
            ->select('notes.id', 'notes.title', 'notes.description', 'notes.pin', 'notes.archive', 'notes.colour', 'labels.labelname')
            ->where('notes.user_id', $user->id)->orWhere('collaborators.email', $user->email)->paginate(4);
        return $notes;
    }

    /**
     * Function to get the pinned notes
     * Passing the user as a parameter
     * 
     * @return array
     */
    public static function getPinnedNotes($user)
    {
        $notes = Note::where('user_id', $user->id)->where('pin', 1)->get();

        return $notes;
    }

    /**
     * Function to get the pinned notes and their labels
     * Passing the user as a parameter
     * 
     * @return array
     */
    public static function getPinnedNotesandItsLabels($user)
    {
        $notes = Note::leftJoin('label_notes', 'label_notes.note_id', '=', 'notes.id')
            ->leftJoin('labels', 'labels.id', '=', 'label_notes.label_id')
            ->select('notes.id', 'notes.title', 'notes.description', 'notes.pin', 'notes.archive', 'notes.colour', 'labels.labelname')
            ->where([['notes.user_id', '=', $user->id], ['pin', '=', 1]])->paginate(4);

        return $notes;
    }

    /**
     * Function to get the Archived Notes
     * Passing the user as a parameter
     * 
     * @return array
     */
    public static function getArchivedNotes($user)
    {
        $notes = Note::where('user_id', $user->id)->where('archive', 1)->get();

        return $notes;
    }

    /**
     * Function to get the archived notes and their labels
     * Passing the user as a parameter
     * 
     * @return array
     */
    public static function getArchivedNotesandItsLabels($user)
    {
        $notes = Note::leftJoin('label_notes', 'label_notes.note_id', '=', 'notes.id')
            ->leftJoin('labels', 'labels.id', '=', 'label_notes.label_id')
            ->select('notes.id', 'notes.title', 'notes.description', 'notes.pin', 'notes.archive', 'notes.colour', 'labels.labelname')
            ->where([['notes.user_id', '=', $user->id], ['archive', '=', 1]])->paginate(4);

        return $notes;
    }

    /**
     * Function to get a searched Note 
     * Passing the Current User Data and Search Key as parameters
     * 
     * @return array
     */
    public static function getSearchedNote($searchKey, $currentUser){
        $usernotes = Note::leftJoin('collaborators', 'collaborators.note_id', '=', 'notes.id')
        ->leftJoin('label_notes', 'label_notes.note_id', '=', 'notes.id')
        ->leftJoin('labels', 'labels.id', '=', 'label_notes.label_id')
        ->select('notes.id', 'notes.title', 'notes.description', 'notes.pin', 'notes.archive', 'notes.colour', 'collaborators.email as Collaborator', 'labels.labelname')
        ->where('notes.user_id', '=', $currentUser->id)->Where('notes.title', 'like', '%' . $searchKey . '%')
        ->orWhere('notes.user_id', '=', $currentUser->id)->Where('notes.description', 'like', '%' . $searchKey . '%')
        ->orWhere('notes.user_id', '=', $currentUser->id)->Where('labels.labelname', 'like', '%' . $searchKey . '%')
        ->orWhere('collaborators.email', '=', $currentUser->email)->Where('notes.title', 'like', '%' . $searchKey . '%')
        ->orWhere('collaborators.email', '=', $currentUser->email)->Where('notes.description', 'like', '%' . $searchKey . '%')
        ->orWhere('collaborators.email', '=', $currentUser->email)->Where('labels.labelname', 'like', '%' . $searchKey . '%')
        ->get();

        return $usernotes;
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
