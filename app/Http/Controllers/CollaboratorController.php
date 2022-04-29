<?php

namespace App\Http\Controllers;

use App\Exceptions\FundoNotesException;
use App\Mail\Mailer;
use App\Models\Collaborator;
use App\Models\Note;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Exception;
use Tymon\JWTAuth\Exceptions\JWTException;

class CollaboratorController extends Controller
{
    /**
     * This function takes User access token and 
     * checks if it is authorised or not and 
     * takes note_id, email if those parameters are valid 
     * it will successfully creates a collaborator.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function addCollaborator(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'note_id' => 'required|integer',
                'email' => 'required|email'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }

            $currentUser = JWTAuth::parseToken()->authenticate();
            $note = Note::getNotesByNoteIdandUserId($request->note_id, $currentUser->id);
            $user = User::getUserByEmail($request->email);

            // $note = Note::where('id', $request->note_id)->first();
            // $user = User::where('email', $request->email)->first();

            if ($currentUser) {
                if ($note) {
                    if ($user) {
                        //$collabUser = Collaborator::where('note_id', $request->note_id)->where('email', $request->email)->get();
                        $collabUser = Collaborator::getCollaborator($request->note_id, $request->email);
                        if ($collabUser != '[]') {
                            Log::info('Collaborator Already Created');
                            throw new FundoNotesException('Collaborator Already Created', 401);
                        }

                        $collab = Collaborator::createCollaborator($request, $currentUser->id);
                        if ($collab) {
                            $mail = new Mailer();
                            $mail->sendEmailToCollab($user, $note, $currentUser);
                            Log::info('Collaborator created Sucessfully');
                            return response()->json([
                                'message' => 'Collaborator created Sucessfully'
                            ], 201);
                        }
                        Log::info('Collab Not Added');
                        throw new FundoNotesException('Collab Not Added', 202);
                    }
                    Log::info('Not a Registered Email');
                    throw new FundoNotesException('Not a Registered Email', 404);
                }
                Log::error('Notes Not Found For User:: ' . $currentUser->id);
                throw new FundoNotesException('Notes Not Found', 404);
            }
            Log::error('Invalid Authorization Token');
            throw new FundoNotesException('Invalid Authorization Token', 401);
        } catch (FundoNotesException $exception) {
            return response()->json([
                'message' => $exception->message()
            ], $exception->statusCode());
        }
    }

    /**
     * This function takes User access token of collaborator and
     * checks if it is authorised or not and 
     * takes note details, if these are valid and authorized user, 
     * updates the notes successfully.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateNoteByCollaborator(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer',
                'title' => 'string|between:3,30',
                'description' => 'string|between:3,1000'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }

            $currentUser = JWTAuth::parseToken()->authenticate();
            $id = $request->only('id');
            $email = $currentUser->email;

            if ($currentUser) {
                $collabUser = Collaborator::where('email', $email)->first();
                if ($collabUser) {
                    $collaborator = Collaborator::getCollaborator($id, $email);

                    if ($collaborator == '[]') {
                        Log::info('Collaborator Not Found');
                        throw new FundoNotesException('Collaborator Not Found', 404);
                    }

                    $notes = Note::getNotesByNoteIdandUserId($id, $collaborator->user_id);
                    if ($notes) {
                        $note = Note::updateCollaboratorNote($notes, $request);
                        if ($note) {
                            Log::info('Notes Updated Successfully');
                            return response()->json([
                                'message' => 'Note Updated Successfully'
                            ], 201);
                        }
                        Log::error('Note Not Updated');
                        throw new FundoNotesException('Note Not Updated', 200);
                    }
                    Log::error('Notes Not Found');
                    throw new FundoNotesException('Notes Not Found', 404);
                }
                Log::error('Collaborator Email Not Found');
                throw new FundoNotesException('Collaborator Email Not Found', 404);
            }
            Log::error('Invalid Authorization Token');
            throw new FundoNotesException('Invalid Authorization Token', 401);
        } catch (FundoNotesException $exception) {
            return response()->json([
                'message' => $exception->message()
            ], $exception->statusCode());
        }
    }

    /**
     * This function takes User access token  and 
     * checks if it is authorised or not and 
     * takes note_id and collabarator email, if these are valid and
     * the user is Authorized, deletes the notes successfully. 
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeCollaborator(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'note_id' => 'required|integer',
                'email' => 'required|email'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }

            $id = $request->only('note_id');
            $email =  $request->only('email');
            $currentUser = JWTAuth::parseToken()->authenticate();
            if ($currentUser) {
                $collaborator = Collaborator::getCollaborator($id, $email);

                if ($collaborator == '[]') {
                    Log::info('Collaborator Not Found');
                    throw new FundoNotesException('Collaborator Not Found', 404);
                }
                $collaboratorDelete = Collaborator::where('note_id', $id)->where('email', $email)->delete();
                if ($collaboratorDelete) {
                    Log::info('Collaborator Deleted Sucessfully');
                    return response()->json([
                        'message' => 'Collaborator Deleted Sucessfully'
                    ], 200);
                }
            }
            Log::error('Invalid Authorization Token');
            throw new FundoNotesException('Invalid Authorization Token', 401);
        } catch (FundoNotesException $exception) {
            return response()->json([
                'message' => $exception->message()
            ], $exception->statusCode());
        }
    }

    /**
     * This function takes User access token and 
     * checks if it is authorised or not, 
     * if authorized, returns all the collabarators if found
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllCollaborators()
    {
        try {
            $currentUser = JWTAuth::parseToken()->authenticate();

            if ($currentUser) {
                $collaborator = Collaborator::where('user_id', $currentUser->id)->get();

                if ($collaborator == '[]') {
                    Log::error('Collaborators Not found');
                    throw new FundoNotesException('Collaborators Not found', 404);
                }
                
                Log::info('Fetched Collaborators Successfully');
                return response()->json([
                    'message' => 'Fetched Collaborators Successfully',
                    'Collaborator' => $collaborator
                ], 201);
            }
            Log::error('Invalid Authorization Token');
            throw new FundoNotesException('Invalid Authorization Token', 401);
        } catch (FundoNotesException $exception) {
            return response()->json([
                'message' => $exception->message()
            ], $exception->statusCode());
        }
    }
}
