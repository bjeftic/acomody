<?php

namespace App\Http\Controllers;

use App\Enums\Fee\FeeType;
use App\Enums\Fee\ChargeType;
use App\Http\Support\ApiResponse;
use App\Http\Resources\Fee\FeeTypeResource;
use App\Http\Resources\Fee\FeeChargeTypeResource;
use Illuminate\Http\JsonResponse;

class FeeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/fees/types",
     *     operationId="getFeeTypes",
     *     tags={"Fee"},
     *     summary="Get fee types list",
     *     description="Returns a list of all fee types",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Fee types retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Fee types retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/FeeType")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="total", type="integer", example=15)
     *             )
     *         )
     *     )
     * )
     */

    public function indexFeeTypes(): JsonResponse
    {
        $feeTypes = collect(FeeType::toArray());

        $meta = [
            'total' => count($feeTypes),
        ];

        return ApiResponse::success(
            'Fee types retrieved successfully.',
            FeeTypeResource::collection($feeTypes),
            $meta
        );
    }

    /**
     * @OA\Get(
     *     path="/fees/charge-types",
     *     operationId="getFeeChargeTypes",
     *     tags={"Fee"},
     *     summary="Get fee charge types list",
     *     description="Returns a list of all fee charge types",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Fee charge types retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Fee charge types retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/FeeChargeType")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="total", type="integer", example=7)
     *             )
     *         )
     *     )
     * )
     */
    public function indexFeeChargeTypes(): JsonResponse
    {
        $chargeTypes = collect(ChargeType::toArray());

        $meta = [
            'total' => count($chargeTypes),
        ];

        return ApiResponse::success(
            'Charge types retrieved successfully.',
            FeeChargeTypeResource::collection($chargeTypes),
            $meta
        );
    }
}
