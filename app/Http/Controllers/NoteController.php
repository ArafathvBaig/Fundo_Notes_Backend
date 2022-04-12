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
use Tymon\JWTAuth\Facades\JWTAuth;


class NoteController extends Controller
{
    /**
     * Takes User access token and checks if it is authorised or not
     * If authorised, get user Id and
     * and create the Note Successfully
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createNote(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:3|max:30',
            'description' => 'required|string|min:3|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = JWTAuth::parseToken()->authenticate();
        //$user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 401,
                'message' => 'Invalid Authorization Token'
            ], 401);
        } else {
            $note = new Note;
            $note->title = $request->input('title');
            $note->description = $request->input('description');
            $note->user_id = $user->id;
            $note->save();
        }
        return response()->json([
            'status' => 201,
            'message' => 'Notes Created Successfully'
        ], 201);
    }

    /**
     * This function takes JWT access token and note id and finds
     * if there is any note existing on that User id and note id if so
     * it successfully returns that note id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function displayNoteById(Request $request)
    {
        $currentUser = JWTAuth::parseToken()->authenticate();
        if ($currentUser) {
            $notes = Note::where('user_id', '=', $currentUser->id)->get();
        }
        if ($notes == '[]') {
            return response()->json([
                'status' => 404,
                'message' => 'Notes Not Found'
            ], 404);
        }

        return response()->json([
            'message' => 'All Notes are Fetched Successfully',
            'Notes' => $notes
        ], 200);
    }

    public function updateNoteById(Request $request)
    {
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
        //$note = $currentUser->notes()->find($id);

        if ($currentUser) {
            $notes = Note::where('user_id', $currentUser->id)->get();
            $notes = Note::where('id', $id)->first();
        }

        if (!$notes) {
            return response()->json([
                'status' => 404,
                'message' => 'Notes Not Found'
            ], 404);
        } else {
            $notes->id = $request->id;
            $notes->user_id = $currentUser->id;
            $notes->title = $request->title;
            $notes->description = $request->description;
        }

        if ($notes->save()) {
            return response()->json([
                'status' => 201,
                'Message' => 'Note Updated Successfully'
            ], 201);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Invalid Authorization Token'
            ], 404);
        }
    }


    public function deleteNoteById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);;
        }

        $id = $request->only('id');
        $currentUser = JWTAuth::parseToken()->authenticate();
        //$note = $currentUser->notes()->find($id);

        if ($currentUser) {
            $notes = Note::where('user_id', $currentUser->id)->get();
            $notes = Note::where('id', $id)->first();
        }

        if (!$notes) {
            return response()->json([
                'status' => 404,
                'message' => 'Notes Not Found'
            ], 404);
        } else {
            if ($notes->delete()) {
                return response()->json([
                    'status' => 201,
                    'message' => 'Note Deleted Successfully'
                ], 201);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'Invalid Authorization Token'
                ], 404);
            }
        }
    }
}
