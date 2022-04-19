<?php

namespace App\Http\Controllers;

use App\Exceptions\FundoNotesException;
use App\Models\Note;
use App\Models\User;
use App\Models\Label;
use App\Models\LabelNotes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;


class NoteController extends Controller
{
    /**
     * @OA\Post(
     *   path="/api/createNote",
     *   summary="Create Note",
     *   description="Create Notes for User",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"title","description"},
     *               @OA\Property(property="title", type="string"),
     *               @OA\Property(property="description", type="string"),  
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Notes Created Successfully"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   security={
     *       {"Bearer": {}}
     *     }
     * )
     * Takes User authorization token and checks if it is authorised or not
     * If authorised, get user Id and
     * and create the Note Successfully
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createNote(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|min:3|max:30',
                'description' => 'required|string|min:3|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'message' => 'Invalid Authorization Token'
                ], 401);
            } else {
                Note::createNote($request, $user->id);
                Log::info('Notes Created Successfully For User::' . $user->id);
                return response()->json([
                    'message' => 'Notes Created Successfully'
                ], 201);
            }
        } catch (JWTException $exception) {
            return response()->json([
                'message' => $exception->getMessage()
            ], 400);
        }
    }

    /**
     * @OA\Get(
     * path="/api/displayNoteById",
     * summary="Display Note",
     * description="Display Notes for a particular ID",
     * @OA\RequestBody(
     *      @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id"},
     *               @OA\Property(property="id", type="integer"),
     *            ),
     *        ),
     * ),
     *   @OA\Response(response=200, description="Notes Fetched Successfully"),
     *   @OA\Response(response=404, description="Notes Not Found"),
     * )
     * This function takes authorization token and note id and finds
     * if there is any note existing on that User id and note id 
     * if exist, it successfully fetch the data and print
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function displayNoteById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $notes = Note::getNoteByNoteId($request->id);
        if (!$notes) {
            return response()->json([
                'message' => 'Notes Not Found'
            ], 404);
        } else {
            return response()->json([
                'message' => 'Notes Fetched Successfully',
                'Notes' => $notes
            ], 200);
        }
    }

    /**
     * @OA\Get(
     * path="/api/displayAllNotes",
     * summary="Display Notes of a User",
     * description="Display Notes of an User",
     * @OA\RequestBody(),
     *   @OA\Response(response=200, description="All Notes are Fetched Successfully"),
     *   @OA\Response(response=404, description="Notes Not Found"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   security={
     *       {"Bearer": {}}
     *   }
     * )
     * This function takes authorization token and finds
     * if there is any note existing on that User id, 
     * if exists, it successfully fetch the notes and print
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function displayAllNotes()
    {
        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
            if (!$currentUser) {
                return response()->json([
                    'message' => 'Invalid Authorization Token'
                ], 401);
            } else {
                $notes = Note::getNotesByUserId($currentUser->id);
                if (!$notes) {
                    return response()->json([
                        'message' => 'Notes Not Found'
                    ], 404);
                } else {
                    return response()->json([
                        'message' => 'All Notes are Fetched Successfully',
                        'Notes' => $notes
                    ], 200);
                }
            }
        } catch (JWTException $exception) {
            return response()->json([
                'message' => $exception->getMessage()
            ], 400);
        }
    }

    /**
     *   @OA\Post(
     *   path="/api/updateNoteById",
     *   summary="update note",
     *   description="update user note",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id","title","description"},
     *               @OA\Property(property="id", type="integer"),
     *               @OA\Property(property="title", type="string"),
     *               @OA\Property(property="description", type="string"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Note Updated Successfully"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   @OA\Response(response=404, description="Notes Not Found"),
     *   security={
     *       {"Bearer": {}}
     *     }
     * )
     * This function takes the User authorization token and
     * Note Id which user wants to update and 
     * finds the note id if it is existed or not. 
     * If so, updates it successfully.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateNoteById(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer',
                'title' => 'required|string|min:3|max:30',
                'description' => 'required|string|min:3|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);;
            }

            $id = $request->only('id');
            $currentUser = JWTAuth::parseToken()->authenticate();

            if (!$currentUser) {
                return response()->json([
                    'message' => 'Invalid Authorization Token'
                ], 401);
            } else {
                $notes = Note::getNotesByNoteIdandUserId($id, $currentUser->id);
                if (!$notes) {
                    return response()->json([
                        'message' => 'Notes Not Found'
                    ], 404);
                } else {
                    $notes = Note::updateNote($notes, $request, $currentUser->id);
                    if ($notes) {
                        return response()->json([
                            'message' => 'Note Updated Successfully'
                        ], 201);
                    }
                }
            }
        } catch (JWTException $exception) {
            return response()->json([
                'message' => $exception->getMessage()
            ], 400);
        }
    }

    /**
     *   @OA\post(
     *   path="/api/deleteNoteById",
     *   summary="Delete Note",
     *   description="Delete User Note",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id"},
     *               @OA\Property(property="id", type="integer"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Note Deleted Successfully"),
     *   @OA\Response(response=404, description="Notes Not Found"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   security={
     *       {"Bearer": {}}
     *     }
     * )
     * This function takes User authorization token and note id.
     * Finds which user wants to delete and 
     * Finds the note id if it is existed or not.
     * If Exists deletes it successfully.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteNoteById(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);;
            }

            $id = $request->only('id');
            $currentUser = JWTAuth::parseToken()->authenticate();

            if (!$currentUser) {
                return response()->json([
                    'message' => 'Invalid Authorization Token'
                ], 401);
            } else {
                $notes = Note::getNotesByNoteIdandUserId($id, $currentUser->id);
                if (!$notes) {
                    return response()->json([
                        'message' => 'Notes Not Found'
                    ], 404);
                } else {
                    if ($notes->delete()) {
                        return response()->json([
                            'message' => 'Note Deleted Successfully'
                        ], 201);
                    }
                }
            }
        } catch (JWTException $exception) {
            return response()->json([
                'message' => $exception->getMessage()
            ], 400);
        }
    }

    /**
     *   @OA\Post(
     *   path="/api/addNoteLabel",
     *   summary="Add Note Label",
     *   description="Add Label to the Note",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"label_id","note_id"},
     *               @OA\Property(property="label_id", type="integer"),
     *               @OA\Property(property="note_id", type="integer"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="LabelNote Added Successfully"),
     *   @OA\Response(response=409, description="Note Already Have This Label"),
     *   @OA\Response(response=404, description="Note or Label Not Found"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   security={
     *       {"Bearer": {}}
     *     }
     * )
     *
     * This Function takes label_id, note_id and authorization token and
     * Finds the user is authorized and having a note_id and label_id as same
     * then add them to the label notes table
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function addNoteLabel(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'label_id' => 'required|integer',
                'note_id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'message' => 'Invalid Authorization Token'
                ], 401);
            } else {
                $notes = Note::getNotesByNoteIdandUserId($request->note_id, $user->id);
                $label = Label::getLabelByLabelIdandUserId($request->label_id, $user->id);
                if (!$notes || !$label) {
                    return response()->json([
                        'message' => 'Note or Label Not Found'
                    ], 404);
                } else {
                    $labelnote = LabelNotes::getLabelNotesbyLabelIdNoteIdandUserId($request, $user->id);
                    if ($labelnote) {
                        return response()->json([
                            'message' => 'Note Already Have This Label'
                        ], 409);
                    } else {
                        LabelNotes::createNoteLabel($request, $user->id);
                        return response()->json([
                            'message' => 'LabelNote Added Successfully'
                        ], 201);
                    }
                }
            }
        } catch (JWTException $exception) {
            return response()->json([
                'message' => $exception->getMessage()
            ], 400);
        }
    }

    /**
     *   @OA\Post(
     *   path="/api/deleteNoteLabel",
     *   summary="Delete Note Label",
     *   description="Delete the Label of a Note",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"label_id","note_id"},
     *               @OA\Property(property="label_id", type="integer"),
     *               @OA\Property(property="note_id", type="integer"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Label Note Successfully Deleted"),
     *   @OA\Response(response=404, description="LabelNotes Not Found With These Credentials"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   security={
     *       {"Bearer": {}}
     *     }
     * )
     *
     * This Function takes label_id, note_id and authorization token and
     * Finds the user is authorized and having a note_id and label_id as same
     * then add them to the label notes table
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteNoteLabel(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'label_id' => 'required|integer',
                'note_id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Invalid Authorization Token'
                ], 401);
            } else {
                $labelnote = LabelNotes::getLabelNotesbyLabelIdNoteIdandUserId($request, $user->id);
                if (!$labelnote) {
                    return response()->json([
                        'message' => 'LabelNotes Not Found With These Credentials'
                    ], 404);
                } else {
                    $labelnote->delete($labelnote->id);
                    return response()->json([
                        'status' => 201,
                        'message' => 'Label Note Successfully Deleted'
                    ], 201);
                }
            }
        } catch (JWTException $exception) {
            return response()->json([
                'message' => $exception->getMessage()
            ], 400);
        }
    }

    /**
     * Pin Note By ID
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function pinNoteById(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer'
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }

            $currentUser = JWTAuth::parseToken()->authenticate();
            if (!$currentUser) {
                return response()->json([
                    'message' => 'Invalid Authorization Token'
                ], 401);
            } else {
                $note = Note::where('id', $request->id)->where('user_id', $currentUser->id)->first();
                if (!$note) {
                    return response()->json([
                        'message' => 'Notes Not Found'
                    ], 404);
                } else {
                    if ($note->pin == 0) {
                        $note->pin = 1;
                        $note->save();
                        return response()->json([
                            'message' => 'Note Pinned Successfully'
                        ], 201);
                    } else {
                        return response()->json([
                            'message' => 'Note Already Pinned'
                        ], 409);
                    }
                }
            }
        } catch (JWTException $exception) {
            return response()->json([
                'message' => $exception->getMessage()
            ], 400);
        }
    }

    /**
     * Archive Note By ID
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function archiveNoteById(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer'
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }

            $currentUser = JWTAuth::parseToken()->authenticate();
            if (!$currentUser) {
                return response()->json([
                    'message' => 'Invalid Authorization Token'
                ], 401);
            } else {
                $note = Note::where('id', $request->id)->where('user_id', $currentUser->id)->first();
                if (!$note) {
                    return response()->json([
                        'message' => 'Notes Not Found'
                    ], 404);
                } else {
                    if ($note->archive == 0) {
                        $note->archive = 1;
                        $note->save();
                        return response()->json([
                            'message' => 'Note Archived Successfully'
                        ], 201);
                    } else {
                        return response()->json([
                            'message' => 'Note Already Archived'
                        ], 409);
                    }
                }
            }
        } catch (JWTException $exception) {
            return response()->json([
                'message' => $exception->getMessage()
            ], 400);
        }
    }
}
