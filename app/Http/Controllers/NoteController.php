<?php

namespace App\Http\Controllers;

use App\Exceptions\FundoNotesException;
use App\Models\Note;
use App\Models\User;
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
     * Takes User access token and checks if it is authorised or not
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
                $note = new Note;
                $note->title = $request->input('title');
                $note->description = $request->input('description');
                $note->user_id = $user->id;
                $note->save();
            }
            Log::info('Notes Created Successfully For User::' . $user->id);
            return response()->json([
                'message' => 'Notes Created Successfully'
            ], 201);
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
     * description="Display Notes For an User",
     * @OA\RequestBody(),
     *   @OA\Response(response=200, description="All Notes are Fetched Successfully"),
     *   @OA\Response(response=404, description="Notes Not Found"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   security={
     *       {"Bearer": {}}
     *   }
     * )
     * This function takes access token and note id and finds
     * if there is any note existing on that User id and note id if so
     * it successfully returns that note id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function displayNoteById(Request $request)
    {
        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
            if (!$currentUser) {
                return response()->json([
                    'message' => 'Invalid Authorization Token'
                ], 401);
            } else {
                $notes = Note::where('user_id', '=', $currentUser->id)->get();
                if ($notes == '[]') {
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
     * This function takes the User access token and
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
                $notes = Note::where('id', $id)->where('user_id', $currentUser->id)->first();
                if (!$notes) {
                    return response()->json([
                        'message' => 'Notes Not Found'
                    ], 404);
                } else {
                    $notes->id = $request->id;
                    $notes->user_id = $currentUser->id;
                    $notes->title = $request->title;
                    $notes->description = $request->description;
                    if ($notes->save()) {
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
     * This function takes User access token and note id.
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
                $notes = Note::where('id', $id)->where('user_id', $currentUser->id)->first();
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
}
