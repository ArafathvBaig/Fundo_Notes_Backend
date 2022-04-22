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
     *   summary="Create Notes",
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
     *               @OA\Property(property="label_id", type="integer"),  
     *               @OA\Property(property="pin", type="boolean"),  
     *               @OA\Property(property="archive", type="boolean"),  
     *               @OA\Property(property="colour")
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Notes Created Successfully"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   @OA\Response(response=404, description="Label Not Found"),
     *   @OA\Response(response=406, description="Colour Not Specified in the List"),
     *   @OA\Response(response=409, description="Pin and Archive Cannot be True at the Same Time"),
     *   security={
     *       {"Bearer": {}}
     *     }
     * )
     * 
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
                'description' => 'required|string|min:3|max:1000',
                'label_id' => 'required|integer',
                'pin' => 'boolean',
                'archive' => 'boolean',
                'colour' => ''
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                Log::error('Invalid Authorization Token');
                throw new FundoNotesException('Invalid Authorization Token', 401);
            } else {
                $label = Label::getLabelByLabelIdandUserId($request->label_id, $user->id);
                if (!$label) {
                    Log::error('Label Not Found');
                    throw new FundoNotesException('Label Not Found', 404);
                } else {
                    if ($request->pin == true && $request->archive == true) {
                        throw new FundoNotesException('Pin and Archive Cannot be True at the Same Time', 409);
                    } else {
                        $colours  =  array(
                            'white' => 'rgb(255,255,255)',
                            'red' => 'rgb(255,0,0)',
                            'orange' => 'rgb(255,165,0)',
                            'green' => 'rgb(0,255,0)',
                            'teal' => 'rgb(0,128,128)',
                            'blue' => 'rgb(0,0,255)',
                            'darkblue' => 'rgb(0,0,139)',
                            'purple' => 'rgb(128,0,128)',
                            'pink' => 'rgb(255,192,203)',
                            'brown' => 'rgb(165,42,42)',
                            'yellow' => 'rgb(255,255,0)',
                            'gray' => 'rgb(128,128,128)',
                        );
                        $colour = strtolower($request->colour);
                        if ($request->colour == '') {
                            $colour = "white";
                        } elseif (isset($colours[$colour])) {
                            $colour = strtolower($request->colour);
                        } else {
                            Log::info('Colour Not Specified in the List');
                            throw new FundoNotesException('Colour Not Specified in the List', 406);
                        }
                        $note_id = Note::createNotes($request, $user->id, $colours[$colour]);
                        LabelNotes::createNoteandLabel($note_id, $request->label_id, $user->id);
                        Log::info('Notes Created Successfully For User::' . $user->id);
                        return response()->json([
                            'message' => 'Notes Created Successfully'
                        ], 201);
                    }
                }
            }
        } catch (FundoNotesException $exception) {
            return response()->json([
                'message' => $exception->message()
            ], $exception->statusCode());
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
     *               @OA\Property(property="label_id", type="integer"),  
     *               @OA\Property(property="pin", type="boolean"),  
     *               @OA\Property(property="archive", type="boolean"),  
     *               @OA\Property(property="colour")
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Note Updated Successfully"),
     *   @OA\Response(response=204, description="Label Not Found"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   @OA\Response(response=403, description="This Note Already Have This Label"),
     *   @OA\Response(response=404, description="Notes Not Found"),
     *   @OA\Response(response=406, description="Colour Not Specified in the List"),
     *   @OA\Response(response=409, description="Pin and Archive Cannot be True at the Same Time"),
     *   security={
     *       {"Bearer": {}}
     *     }
     * )
     * 
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
                'description' => 'required|string|min:3|max:100',
                'label_id' => 'required|integer',
                'pin' => 'boolean',
                'archive' => 'boolean',
                'colour' => ''
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);;
            }

            $id = $request->only('id');
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                Log::error('Invalid Authorization Token');
                throw new FundoNotesException('Invalid Authorization Token', 401);
            } else {
                $notes = Note::getNotesByNoteIdandUserId($id, $user->id);
                if (!$notes) {
                    Log::error('Notes Not Found');
                    throw new FundoNotesException('Notes Not Found', 404);
                } else {
                    $label = Label::getLabelByLabelIdandUserId($request->label_id, $user->id);
                    if (!$label) {
                        Log::error('Label Not Found');
                        throw new FundoNotesException('Label Not Found', 204);
                    } else {
                        $labelnote = LabelNotes::getLabelNotesbyLabelIdNoteIdandUserId($request->label_id, $request->id, $user->id);
                        if ($labelnote) {
                            Log::error('This Note Already Have This Label');
                            throw new FundoNotesException('This Note Already Have This Label', 403);
                        } else {
                            if ($request->pin == true && $request->archive == true) {
                                Log::error('Pin and Archive Cannot be True at the Same Time');
                                throw new FundoNotesException('Pin and Archive Cannot be True at the Same Time', 409);
                            } else {
                                $colours  =  array(
                                    'white' => 'rgb(255,255,255)',
                                    'red' => 'rgb(255,0,0)',
                                    'orange' => 'rgb(255,165,0)',
                                    'green' => 'rgb(0,255,0)',
                                    'teal' => 'rgb(0,128,128)',
                                    'blue' => 'rgb(0,0,255)',
                                    'darkblue' => 'rgb(0,0,139)',
                                    'purple' => 'rgb(128,0,128)',
                                    'pink' => 'rgb(255,192,203)',
                                    'brown' => 'rgb(165,42,42)',
                                    'yellow' => 'rgb(255,255,0)',
                                    'gray' => 'rgb(128,128,128)',
                                );
                                $colour = strtolower($request->colour);
                                if ($request->colour == '') {
                                    $colour = "white";
                                } elseif (isset($colours[$colour])) {
                                    $colour = strtolower($request->colour);
                                } else {
                                    Log::info('Colour Not Specified in the List');
                                    throw new FundoNotesException('Colour Not Specified in the List', 406);
                                }
                                LabelNotes::createNoteandLabel($request->id, $request->label_id, $user->id);
                                $notes = Note::updateNote($notes, $request, $user->id, $colours[$colour]);
                                Log::info('Notes Updated Successfully');
                                if ($notes) {
                                    return response()->json([
                                        'message' => 'Note Updated Successfully'
                                    ], 201);
                                }
                            }
                        }
                    }
                }
            }
        } catch (FundoNotesException $exception) {
            return response()->json([
                'message' => $exception->message()
            ], $exception->statusCode());
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
     *               @OA\Property(property="id", type="integer")
     *            ),
     *        ),
     * ),
     *   @OA\Response(response=200, description="Notes Fetched Successfully"),
     *   @OA\Response(response=404, description="Notes Not Found"),
     * )
     * 
     * This function takes authorization token and note id and finds
     * if there is any note existing on that User id and note id 
     * if exist, it successfully fetch the data and print
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function displayNoteById(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            $notes = Note::getNoteByNoteId($request->id);
            if (!$notes) {
                Log::error('Notes Not Found');
                throw new FundoNotesException('Notes Not Found', 404);
            } else {
                Log::info('Notes Fetched Successfully');
                return response()->json([
                    'message' => 'Notes Fetched Successfully',
                    'Notes' => $notes
                ], 200);
            }
        } catch (FundoNotesException $exception) {
            return response()->json([
                'message' => $exception->message()
            ], $exception->statusCode());
        }
    }

    /**
     * @OA\Get(
     * path="/api/displayAllNotes",
     * summary="Display Notes of a User and Labels of a note",
     * description="Display Notes of an User and Labels of that note",
     * @OA\RequestBody(),
     *   @OA\Response(response=200, description="All Notes are Fetched Successfully"),
     *   @OA\Response(response=404, description="Notes Not Found"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   security={
     *       {"Bearer": {}}
     *   }
     * )
     * 
     * This function takes authorization token and finds
     * if there is any note existing on that User id and
     * it successfully fetch the notes and print
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function displayAllNotes(Request $request)
    {
        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
            if (!$currentUser) {
                Log::error('Invalid Authorization Token');
                throw new FundoNotesException('Invalid Authorization Token', 401);
            } else {
                $notes = Note::getNotesandItsLabels($currentUser);
                if (!$notes) {
                    Log::error('Notes Not Found');
                    throw new FundoNotesException('Notes Not Found', 404);
                } else {
                    Log::info('All Notes are Fetched Successfully for User:: ' . $currentUser->id);
                    return response()->json([
                        'message' => 'All Notes are Fetched Successfully',
                        'Notes' => $notes
                    ], 200);
                }
            }
        } catch (FundoNotesException $exception) {
            return response()->json([
                'message' => $exception->message()
            ], $exception->statusCode());
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
     *               @OA\Property(property="id", type="integer")
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
     * 
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
                Log::error('Invalid Authorization Token');
                throw new FundoNotesException('Invalid Authorization Token', 401);
            } else {
                $notes = Note::getNotesByNoteIdandUserId($id, $currentUser->id);
                if (!$notes) {
                    Log::error('Notes Not Found');
                    throw new FundoNotesException('Notes Not Found', 404);
                } else {
                    Log::info('Note Deleted Successfully');
                    if ($notes->delete()) {
                        return response()->json([
                            'message' => 'Note Deleted Successfully'
                        ], 201);
                    }
                }
            }
        } catch (FundoNotesException $exception) {
            return response()->json([
                'message' => $exception->message()
            ], $exception->statusCode());
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
     *               @OA\Property(property="note_id", type="integer")
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
                Log::error('Invalid Authorization Token');
                throw new FundoNotesException('Invalid Authorization Token', 401);
            } else {
                $notes = Note::getNotesByNoteIdandUserId($request->note_id, $user->id);
                $label = Label::getLabelByLabelIdandUserId($request->label_id, $user->id);
                if (!$notes || !$label) {
                    Log::error('Note or Label Not Found');
                    throw new FundoNotesException('Notes or Label Not Found', 404);
                } else {
                    $labelnote = LabelNotes::getLabelNotesbyLabelIdNoteIdandUserId($request->label_id, $request->note_id, $user->id);
                    if ($labelnote) {
                        Log::info('Note Already Have This Label');
                        throw new FundoNotesException('Note Already Have This Label', 409);
                    } else {
                        LabelNotes::createNoteLabel($request, $user->id);
                        Log::info('LabelNote Added Successfully');
                        return response()->json([
                            'message' => 'LabelNote Added Successfully'
                        ], 201);
                    }
                }
            }
        } catch (FundoNotesException $exception) {
            return response()->json([
                'message' => $exception->message()
            ], $exception->statusCode());
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
     *               @OA\Property(property="note_id", type="integer")
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
                Log::error('Invalid Authorization Token');
                throw new FundoNotesException('Invalid Authorization Token', 401);
            } else {
                $labelnote = LabelNotes::getLabelNotesbyLabelIdNoteIdandUserId($request->label_id, $request->note_id, $user->id);
                if (!$labelnote) {
                    Log::error('LabelNotes Not Found With These Credentials');
                    throw new FundoNotesException('LabelNotes Not Found With These Credentials', 404);
                } else {
                    $labelnote->delete($labelnote->id);
                    Log::info('Label Note Successfully Deleted');
                    return response()->json([
                        'message' => 'Label Note Successfully Deleted',
                        'NoteLabel' => $labelnote
                    ], 201);
                }
            }
        } catch (FundoNotesException $exception) {
            return response()->json([
                'message' => $exception->message()
            ], $exception->statusCode());
        }
    }

    /**
     * @OA\Post(
     *   path="/api/pinNoteById",
     *   summary="Pin Note by id",
     *   description=" Pin Note by its id ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id"},
     *               @OA\Property(property="id", type="integer")
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Note Pinned Sucessfully"),
     *   @OA\Response(response=404, description="Notes Not Found"),
     *   @OA\Response(response=409, description="Note Already Pinned"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   security = {
     *      {"Bearer" : {}}
     *  }
     * )
     * 
     * This function takes the User access token and checks if it
     * authorised or not and it takes the note_id and pin  it
     * successfully if notes exist.
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
                Log::error('Invalid Authorization Token');
                throw new FundoNotesException('Invalid Authorization Token', 401);
            } else {
                $note = Note::getNotesByNoteIdandUserId($request->id, $currentUser->id);
                if (!$note) {
                    Log::error('Notes Not Found');
                    throw new FundoNotesException('Notes Not Found', 404);
                } else {
                    if ($note->pin == 0) {
                        if ($note->archive == 1) {
                            $note->archive = 0;
                            $note->save();
                        }
                        $note->pin = 1;
                        $note->save();
                        Log::info('Note Pinned Successfully');
                        return response()->json([
                            'message' => 'Note Pinned Successfully'
                        ], 201);
                    } else {
                        Log::info('Note Already Pinned');
                        throw new FundoNotesException('Note Already Pinned', 409);
                    }
                }
            }
        } catch (FundoNotesException $exception) {
            return response()->json([
                'message' => $exception->message()
            ], $exception->statusCode());
        }
    }

    /**
     * @OA\Post(
     *   path="/api/unPinNoteById",
     *   summary="Unpin Note by id",
     *   description=" Unpin Note ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id"},
     *               @OA\Property(property="id", type="integer")
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Note UnPinned Successfully"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   @OA\Response(response=404, description="Notes Not Found"),
     *   @OA\Response(response=409, description="Note Already UnPinned"),
     *   security = {
     *      {"Bearer" : {}}
     *   }
     * )
     * 
     * This function takes the User access token and checks if it
     * authorised or not and it takes the note_id and unpin  it
     * successfully if notes exist.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unPinNoteById(Request $request)
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
                Log::error('Invalid Authorization Token');
                throw new FundoNotesException('Invalid Authorization Token', 401);
            } else {
                $note = Note::getNotesByNoteIdandUserId($request->id, $currentUser->id);
                if (!$note) {
                    Log::error('Notes Not Found');
                    throw new FundoNotesException('Notes Not Found', 404);
                } else {
                    if ($note->pin == 1) {
                        $note->pin = 0;
                        $note->save();
                        Log::info('Note UnPinned Successfully');
                        return response()->json([
                            'message' => 'Note UnPinned Successfully'
                        ], 201);
                    } else {
                        Log::info('Note Already UnPinned');
                        throw new FundoNotesException('Note Already UnPinned', 409);
                    }
                }
            }
        } catch (FundoNotesException $exception) {
            return response()->json([
                'message' => $exception->message()
            ], $exception->statusCode());
        }
    }

    /**
     * @OA\Get(
     *   path="/api/getAllPinnedNotes",
     *   summary="Display All Pinned Notes",
     *   description=" Display All Pinned Notes",
     *   @OA\RequestBody(),
     *   @OA\Response(response=404, description="Notes Not Found"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   @OA\Response(response=200, description="Fetched All Pinned Notes Successfully"),
     *   security = {
     *      {"Bearer" : {}}
     *   }
     * )
     * 
     * This function takes the User access token and 
     * checks if it authorised or not. 
     * If Authorized, it returns all the pinned notes successfully.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllPinnedNotes()
    {
        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
            if (!$currentUser) {
                Log::error('Invalid Authorization Token');
                throw new FundoNotesException('Invalid Authorization Token', 401);
            } else {
                $userNotes = Note::getPinnedNotesandItsLabels($currentUser);
                if (!$userNotes) {
                    Log::error('Notes Not Found');
                    throw new FundoNotesException('Notes Not Found', 404);
                }
                return response()->json([
                    'message' => 'Fetched All Pinned Notes Successfully',
                    'notes' => $userNotes
                ], 200);
            }
        } catch (FundoNotesException $exception) {
            return response()->json([
                'message' => $exception->message()
            ], $exception->statusCode());
        }
    }

    /**
     * @OA\Post(
     *   path="/api/archiveNoteById",
     *   summary="Archive Note by id",
     *   description="Archive Note by its id ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id"},
     *               @OA\Property(property="id", type="integer")
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Note Archive Sucessfully"),
     *   @OA\Response(response=404, description="Notes Not Found"),
     *   @OA\Response(response=409, description="Note Already Archived"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   security = {
     *      {"Bearer" : {}}
     *  }
     * )
     * 
     * This function takes the User access token and checks if it
     * authorised or not and it takes the note_id and archive it
     * successfully if notes exist.
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
                Log::error('Invalid Authorization Token');
                throw new FundoNotesException('Invalid Authorization Token', 401);
            } else {
                $note = Note::getNotesByNoteIdandUserId($request->id, $currentUser->id);
                if (!$note) {
                    Log::error('Notes Not found');
                    throw new FundoNotesException('Notes Not Found', 404);
                } else {
                    if ($note->archive == 0) {
                        if ($note->pin == 1) {
                            $note->pin = 0;
                            $note->save();
                        }
                        $note->archive = 1;
                        $note->save();
                        Log::info('Note Archived Successfully');
                        return response()->json([
                            'message' => 'Note Archived Successfully'
                        ], 201);
                    } else {
                        Log::info('Note Already Archived');
                        throw new FundoNotesException('Note Already Archived', 409);
                    }
                }
            }
        } catch (FundoNotesException $exception) {
            return response()->json([
                'message' => $exception->message()
            ], $exception->statusCode());
        }
    }

    /**
     * @OA\Post(
     *   path="/api/unArchiveNoteById",
     *   summary="Unarchive Note by id",
     *   description=" Unarchive Note ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id"},
     *               @OA\Property(property="id", type="integer")
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Note UnArchived Successfully"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   @OA\Response(response=404, description="Notes Not Found"),
     *   @OA\Response(response=409, description="Note Already UnArchived"),
     *   security = {
     *      {"Bearer" : {}}
     *   }
     * )
     * 
     * This function takes the User access token and checks if it
     * authorised or not and it takes the note_id and unarchive it
     * successfully if notes exist.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unArchiveNoteById(Request $request)
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
                Log::error('Invalid Authorization Token');
                throw new FundoNotesException('Invalid Authorization Token', 401);
            } else {
                $note = Note::getNotesByNoteIdandUserId($request->id, $currentUser->id);
                if (!$note) {
                    Log::error('Notes Not Found');
                    throw new FundoNotesException('Notes Not Found', 404);
                } else {
                    if ($note->archive == 1) {
                        $note->archive = 0;
                        $note->save();
                        Log::info('Note UnArchived Successfully');
                        return response()->json([
                            'message' => 'Note UnArchived Successfully'
                        ], 201);
                    } else {
                        Log::info('Note Already UnPinned');
                        throw new FundoNotesException('Note Already UnArchived', 409);
                    }
                }
            }
        } catch (FundoNotesException $exception) {
            return response()->json([
                'message' => $exception->message()
            ], $exception->statusCode());
        }
    }

    /**
     * @OA\Get(
     *   path="/api/getAllArchivedNotes",
     *   summary="Display All Archived Notes",
     *   description=" Display All Archived Notes",
     *   @OA\RequestBody(),
     *   @OA\Response(response=404, description="Notes Not Found"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   @OA\Response(response=200, description="Fetched All Archived Notes Successfully"),
     *   security = {
     *      {"Bearer" : {}}
     *   }
     * )
     * 
     * This function takes the User access token and 
     * checks if it authorised or not. 
     * If Authorized, it returns all the archived notes successfully.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllArchivedNotes()
    {
        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
            if (!$currentUser) {
                Log::error('Invalid Authorization Token');
                throw new FundoNotesException('Invalid Authorization Token', 401);
            } else {
                $userNotes = Note::getArchivedNotesandItsLabels($currentUser);
                if (!$userNotes) {
                    Log::error('Notes Not Found');
                    throw new FundoNotesException('Notes Not Found', 404);
                }
                return response()->json([
                    'message' => 'Fetched All Archived Notes Successfully',
                    'notes' => $userNotes
                ], 200);
            }
        } catch (FundoNotesException $exception) {
            return response()->json([
                'message' => $exception->message()
            ], $exception->statusCode());
        }
    }

    /**
     * @OA\Post(
     *   path="/api/colourNoteById",
     *   summary="Colour Note",
     *   description=" Colour Note ",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id" , "colour"},
     *               @OA\Property(property="id", type="integer"),
     *               @OA\Property(property="colour", type="string")
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Note Coloured Sucessfully"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   @OA\Response(response=404, description="Notes Not Found"),
     *   @OA\Response(response=406, description="Colour Not Specified in the List"),
     *   security = {
     *      {"Bearer" : {}}
     *   }
     * )
     * 
     * This function takes the User access token and 
     * checks if it authorised or not and it takes the note_id and 
     * colours it successfully if notes exist.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function colourNoteById(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer',
                'colour' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }

            $currentUser = JWTAuth::parseToken()->authenticate();

            if (!$currentUser) {
                Log::error('Invalid Authorization Token');
                throw new FundoNotesException('Invalid Authorization Token', 401);
            } else {
                $note = Note::getNotesByNoteIdandUserId($request->id, $currentUser->id);
                if (!$note) {
                    Log::error('Notes Not Found', ['user' => $currentUser, 'id' => $request->id]);
                    throw new FundoNotesException('Notes Not Found', 404);
                } else {
                    $colours  =  array(
                        'white' => 'rgb(255,255,255)',
                        'red' => 'rgb(255,0,0)',
                        'orange' => 'rgb(255,165,0)',
                        'green' => 'rgb(0,255,0)',
                        'teal' => 'rgb(0,128,128)',
                        'blue' => 'rgb(0,0,255)',
                        'darkblue' => 'rgb(0,0,139)',
                        'purple' => 'rgb(128,0,128)',
                        'pink' => 'rgb(255,192,203)',
                        'brown' => 'rgb(165,42,42)',
                        'yellow' => 'rgb(255,255,0)',
                        'gray' => 'rgb(128,128,128)',
                    );

                    $colour = strtolower($request->colour);

                    if (isset($colours[$colour])) {
                        $note->colour = $colours[$colour];
                        $note->save();

                        Log::info('Notes Coloured Successfully', ['user_id' => $currentUser, 'note_id' => $request->id]);
                        return response()->json([
                            'message' => 'Note Coloured Sucessfully'
                        ], 201);
                    } else {
                        throw new FundoNotesException('Colour Not Specified in the List', 406);
                    }
                }
            }
        } catch (FundoNotesException $exception) {
            return response()->json([
                'message' => $exception->message()
            ], $exception->statusCode());
        }
    }
}
