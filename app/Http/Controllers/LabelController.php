<?php

namespace App\Http\Controllers;

use App\Models\Label;
use App\Models\LabelNotes;
use App\Models\Note;
use App\Exceptions\FundoNotesException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class LabelController extends Controller
{
    /**
     *   @OA\Post(
     *   path="/api/createLabel",
     *   summary="create label",
     *   description="create user label",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"labelname"},
     *               @OA\Property(property="labelname", type="string")
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Label Added Sucessfully"),
     *   @OA\Response(response=409, description="Label Name Already Exists"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   @OA\Response(response=202, description="Label Not Added"),
     *   security={
     *       {"Bearer": {}}
     *   }
     * )
     * 
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
                Log::error('Invalid Authorization Token');
                throw new FundoNotesException('Invalid Authorization Token', 401);
            } else {
                $label = Label::getLabelByLabelNameandUserId($request->labelname, $user->id);
                if ($label) {
                    Log::info('Label Name Already Exists');
                    throw new FundoNotesException('Label Name Already Exists', 409);
                } else {
                    $label = Label::createLabel($request->labelname, $user->id);
                    if ($label) {
                        Log::info('Label Added Sucessfully');
                        return response()->json([
                            'message' => 'Label Added Sucessfully',
                        ], 201);
                    } else {
                        Log::info('Label Not Added');
                        throw new FundoNotesException('Label Not Added', 202);
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
     *   @OA\Get(
     *   path="/api/readAllLabels",
     *   summary="Read All Labels for an User",
     *   description="Read User Labels",
     *   @OA\RequestBody(),
     *   @OA\Response(response=201, description="Labels Retrieved Successfully."),
     *   @OA\Response(response=401, description="Invalid authorization token"),
     *   @OA\Response(response=404, description="Labels Not Found"),
     *   security={
     *       {"Bearer": {}}
     *   }
     * )
     * 
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
                Log::error('Invalid Authorization Token');
                throw new FundoNotesException('Invalid Authorization Token', 401);
            } else {
                Cache::put('labels', Label::getLabelsByUserId($user->id), 60 * 60 * 24);
                $labels = Cache::get('labels');

                // $labels = Cache::remember('labels', 60 * 60 * 24, function () {
                //     return Label::where('user_id', Auth::user()->id)->get();
                // });

                //$labels = Label::getLabelsByUserId($user->id);
                if (!$labels) {
                    Log::error('Labels Not Found');
                    throw new FundoNotesException('Labels Not Found', 404);
                } else {
                    Log::info('Labels Retrieved Successfully.');
                    return response()->json([
                        'message' => 'Labels Retrieved Successfully.',
                        'Label' => $labels
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
     *   @OA\Post(
     *   path="/api/updateLabel",
     *   summary="update label",
     *   description="update user label",
     *   @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"id","labelname"},
     *               @OA\Property(property="id", type="integer"),
     *               @OA\Property(property="labelname", type="string")
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Label Updated Successfully"),
     *   @OA\Response(response=202, description="Label Not Updated"),
     *   @OA\Response(response=404, description="Label Not Found"),
     *   @OA\Response(response=409, description="Label Name Already Exists"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   security={
     *       {"Bearer": {}}
     *   }
     * )
     * 
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
                Log::error('Invalid Authorization Token');
                throw new FundoNotesException('Invalid Authorization Token', 401);
            } else {
                $label = Label::getLabelByLabelIdandUserId($request->id, $user->id);
                if (!$label) {
                    Log::error('Label Not Found');
                    throw new FundoNotesException('Label Not Found', 404);
                } else {
                    if ($label->labelname != $request->labelname) {
                        $label = Label::updateLabel($request->id, $request->labelname, $user->id);

                        Cache::forget('labels');
                        Cache::forget('notes');

                        if ($label) {
                            Log::info('Label Updated Successfully');
                            return response()->json([
                                'message' => "Label Updated Successfully"
                            ], 201);
                        } else {
                            Log::info('Label Not Updated');
                            throw new FundoNotesException('Label Not Updated', 202);
                        }
                    } else {
                        Log::info('Label Name Already Exists');
                        throw new FundoNotesException('Label Name Already Exists', 409);
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
     *   path="/api/deleteLabel",
     *   summary="Delete Label",
     *   description="Delete User Label",
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
     *   @OA\Response(response=200, description="Label Successfully Deleted"),
     *   @OA\Response(response=404, description="Label Not Found"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   security={
     *       {"Bearer": {}}
     *   }
     * )
     *
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
                Log::error('Invalid Authorization Token');
                throw new FundoNotesException('Invalid Authorization Token', 401);
            } else {
                $labels = Label::getLabelByLabelIdandUserId($request->id, $user->id);
                if (!$labels) {
                    Log::error('Label Not Found');
                    throw new FundoNotesException('Label Not Found', 404);
                } else {
                    Cache::forget('labels');
                    Cache::forget('notes');

                    $labels->delete($labels->id);
                    Log::info('Label Successfully Deleted');
                    return response()->json([
                        'message' => 'Label Successfully Deleted'
                    ], 200);
                }
            }
        } catch (FundoNotesException $exception) {
            return response()->json([
                'message' => $exception->message()
            ], $exception->statusCode());
        }
    }
}
