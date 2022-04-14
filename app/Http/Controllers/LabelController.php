<?php

namespace App\Http\Controllers;

use App\Models\Label;
use App\Models\LabelNotes;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class LabelController extends Controller
{
    /**
     * This function takes User access token and 
     * checks if it is authorised or not.
     * If authorised and no label with same name,
     * then a new label is created.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createLabel(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'labelname' => 'required|string|between:2,15'
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
                $labelName = Label::where('labelname', $request->labelname)->where('user_id', $user->id)->first();
                if ($labelName) {
                    return response()->json([
                        'message' => 'Label Name already exists'
                    ], 409);
                } else {
                    $label = new Label();
                    $label->labelname = $request->get('labelname');
                    if ($user->labels()->save($label)) {
                        return response()->json([
                            'message' => 'Label added Sucessfully',
                        ], 201);
                    } else {
                        return response()->json([
                            'message' => 'Label Not Added',
                        ], 202);
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
     * This function takes access token and 
     * finds if there is any label existing on that User id.
     * If there are labels return them.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function readAllLabels()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json([
                    'message' => 'Invalid authorization token'
                ], 401);
            } else {
                $label = Label::where('user_id', $user->id)->get();
                if (!$label) {
                    return response()->json([
                        'message' => 'Notes not found'
                    ], 404);
                } else {
                    return response()->json([
                        'message' => 'Labels Retrieved Successfully.',
                        'Label' => $label
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
     * This function takes the User access token and label id which
     * user wants to update and finds the label id if it is existed
     * or not if so, updates it successfully.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateLabel(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer',
                'labelname' => 'required|string|between:2,15'
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
                $label = Label::where('id', $request->id)->where('user_id', $user->id)->first();
                if (!$label) {
                    return response()->json([
                        'message' => 'Label Not Found'
                    ], 404);
                } else {
                    if ($label->labelname != $request->labelname) {
                        $label->id = $request->id;
                        $label->labelname = $request->labelname;
                        if ($label->save()) {
                            return response()->json([
                                'message' => "Label Updated Successfully"
                            ], 201);
                        } else {
                            return response()->json([
                                'message' => "Label Not Updated"
                            ], 202);
                        }
                    } else {
                        return response()->json([
                            'message' => 'Label Name already exists'
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
     * This function takes the User access token and label id.
     * Authenticate the user and Find the label id if it is existed
     * Delete label if user is Authenticated and label is present.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteLabel(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json([
                    'message' => 'Invalid authorization token'
                ], 401);
            } else {
                $labels = Label::where('id', $request->id)->where('user_id', $user->id)->first();
                if (!$labels) {
                    return response()->json([
                        'message' => 'Label Not Found'
                    ], 404);
                } else {
                    $labels->delete($labels->id);
                    return response()->json([
                        'message' => 'Label Successfully Deleted'
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
                $notes = Note::where('id', $request->note_id)->where('user_id', $user->id)->first();
                if (!$notes) {
                    return response()->json([
                        'message' => 'Note Not Found'
                    ], 404);
                } else {
                    $label = Label::where('id', $request->label_id)->where('user_id', $user->id)->first();
                    if (!$label) {
                        return response()->json([
                            'message' => 'Label Not Found'
                        ], 404);
                    } else {
                        $labelnote = LabelNotes::where('note_id', $request->note_id)->where('label_id', $request->label_id)->first();
                        if ($labelnote) {
                            return response()->json([
                                'message' => 'Note Already Have This Label'
                            ], 409);
                        } else {
                            $labelnotes = LabelNotes::create([
                                'user_id' => $user->id,
                                'note_id' => $request->note_id,
                                'label_id' => $request->label_id
                            ]);
                            return response()->json([
                                'message' => 'LabelNote Created Successfully'
                            ], 201);
                        }
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
                    'message' => 'Invalid authorization token'
                ], 401);
            } else {
                $labelnote = LabelNotes::where('label_id', $request->label_id)->where('note_id', $request->note_id)->first();
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
}
