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
     *               @OA\Property(property="labelname", type="string"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Label Added Sucessfully"),
     *   @OA\Response(response=409, description="Label Name Already Exists"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   @OA\Response(response=202, description="Label Not Added"),
     *   security={
     *       {"Bearer": {}}
     *     }
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
                return response()->json([
                    'message' => 'Invalid Authorization Token'
                ], 401);
            } else {
                $label = Label::getLabelByLabelNameandUserId($request->labelname, $user->id);
                if ($label) {
                    return response()->json([
                        'message' => 'Label Name Already Exists'
                    ], 409);
                } else {
                    $label = Label::createLabel($request->labelname, $user->id);
                    if ($label) {
                        return response()->json([
                            'message' => 'Label Added Sucessfully',
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
     *   @OA\Get(
     *   path="/api/readAllLabels",
     *   summary="Read All Labels for an User",
     *   description="Read User Labels",
     *   @OA\RequestBody(),
     *   @OA\Response(response=201, description="Labels Retrieved Successfully."),
     *   @OA\Response(response=401, description="Invalid authorization token"),
     *   @OA\Response(response=404, description="Notes Not Found"),
     *   security={
     *       {"Bearer": {}}
     *     }
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
                return response()->json([
                    'message' => 'Invalid authorization token'
                ], 401);
            } else {
                $label = Label::getLabelsByUserId($user->id);
                if (!$label) {
                    return response()->json([
                        'message' => 'Labels Not Found'
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
     *               @OA\Property(property="labelname", type="string"),
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
     *     }
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
                return response()->json([
                    'message' => 'Invalid Authorization Token'
                ], 401);
            } else {
                $label = Label::getLabelByLabelIdandUserId($request->id, $user->id);
                if (!$label) {
                    return response()->json([
                        'message' => 'Label Not Found'
                    ], 404);
                } else {
                    if ($label->labelname != $request->labelname) {
                        $label = Label::updateLabel($request->id, $request->labelname, $user->id);
                        if ($label) {
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
                            'message' => 'Label Name Already Exists'
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
     *               @OA\Property(property="id", type="integer"),
     *            ),
     *        ),
     *    ),
     *   @OA\Response(response=201, description="Label Successfully Deleted"),
     *   @OA\Response(response=404, description="Label Not Found"),
     *   @OA\Response(response=401, description="Invalid Authorization Token"),
     *   security={
     *       {"Bearer": {}}
     *     }
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
                return response()->json([
                    'message' => 'Invalid Authorization Token'
                ], 401);
            } else {
                $labels = Label::getLabelByLabelIdandUserId($request->id, $user->id);
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
}
