<?php

namespace App\Http\Controllers;

use App\Exceptions\FundoNotesException;
use App\Mail\Mailer;
use App\Models\Collaborator;
use App\Models\Note;
use App\Models\User;
use App\Notifications\MailToCollab;
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
     * @OA\Post(
     *   path="/api/addCollaborator",
     *   summary="Add Colaborator to a specific Note ",
     *   description=" Add Colaborator a to specific Note ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"email" , "note_id"},
     *               @OA\Property(property="email", type="email"),
     *               @OA\Property(property="note_id", type="integer")
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Collaborator Created Sucessfully"),
     *   @OA\Response(response=202, description="Collab Not Added"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   @OA\Response(response=404, description="Not a Registered Email"),
     *   @OA\Response(response=409, description="Collaborator Already Created"),
     *   security = {
     *      {"Bearer" : {}}
     *   }
     * )
     * 
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
                        if ($collabUser) {
                            Log::info('Collaborator Already Created');
                            throw new FundoNotesException('Collaborator Already Created', 409);
                        }

                        $collab = Collaborator::createCollaborator($request->note_id, $request->email, $currentUser->id);
                        $collaborator = Note::select('id', 'title', 'description')->where('id', $request->note_id)->first();
                        if ($collab) {

                            $delay = now()->addSeconds(60);
                            $user->notify((new MailTocollab($currentUser->email, $collaborator))->delay($delay));

                            // $mail = new Mailer();
                            // $mail->sendEmailToCollab($user, $note, $currentUser);
                            Log::info('Collaborator created Sucessfully');
                            return response()->json([
                                'message' => 'Collaborator Created Sucessfully'
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
     * @OA\Post(
     *   path="/api/updateNoteByCollaborator",
     *   summary="Edit and Update the note by Colaborator",
     *   description="Edit and Update the note by Colaborator",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"title" , "note_id" , "description"},
     *               @OA\Property(property="note_id", type="integer"),
     *               @OA\Property(property="title", type="string"),
     *               @OA\Property(property="description", type="string")
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Note Updated Sucessfully"),
     *   @OA\Response(response=200, description="Note Not Updated"),
     *   @OA\Response(response=404, description="Collaborator Not Found"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   security = {
     *      {"Bearer" : {}}
     *   }
     * )
     * 
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
            $id = $request->input('id');
            $email = $currentUser->email;

            if ($currentUser) {
                $collabUser = Collaborator::where('email', $email)->first();
                if ($collabUser) {
                    $collaborator = Collaborator::getCollaborator($id, $email);

                    if (!$collaborator) {
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
     * @OA\Post(
     *   path="/api/removeCollaborator",
     *   summary="Remove Colaborator from specific Note ",
     *   description=" Remove Colaborator from specific Note ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"email" , "note_id"},
     *               @OA\Property(property="email", type="email"),
     *               @OA\Property(property="note_id", type="integer")
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=200, description="Collaborator Deleted Sucessfully"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   @OA\Response(response=404, description="Collaborator Not Found"),
     *   security = {
     *      {"Bearer" : {}}
     *   }
     * )
     * 
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

            $id = $request->input('note_id');
            $email =  $request->input('email');
            $currentUser = JWTAuth::parseToken()->authenticate();
            if ($currentUser) {
                $collaborator = Collaborator::getCollaborator($id, $email);

                if (!$collaborator) {
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
