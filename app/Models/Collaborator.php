<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collaborator extends Model
{
    use HasFactory;

    protected $table = "collaborators";
    protected $fillable = [
        'email',
    ];

    /**
     * Create a new Collaborator with the given attributes
     * for the user id and its note given
     * 
     * @return array
     */
    public static function createCollaborator($request, $user_id)
    {
        $collab = new Collaborator();
        $collab->note_id = $request->get('note_id');
        $collab->email = $request->get('email');
        $collab->user_id = $user_id;
        $collab->save();
        return $collab;

        // $collab = Collaborator::create([
        //     'note_id' => $request->note_id,
        //     'email' => $request->email,
        //     'user_id' => $user_id
        // ]);
        //return $collab;
    }

    /**
     * Function to get Collaborator by Note_Id and Mail
     * Passing the Note_id and Mail as the parameter
     * 
     * @return array
     */
    public static function getCollaborator($note_id, $email)
    {
        $collabUser = Collaborator::where('note_id', $note_id)->where('email', $email)->get();
        return $collabUser;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function note()
    {
        return $this->belongsTo(Note::class);
    }
}
