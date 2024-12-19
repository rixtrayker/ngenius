<?php

namespace Jeybin\Networkintl\App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Validator;
use Jeybin\Networkintl\App\Services\RecurringPaymentService;

final class RecurringPaymentController {
    
    private $recurringPaymentService;

    public function __construct() {
        $this->recurringPaymentService = new RecurringPaymentService();
    }

    public function create(array $request) {
        try {
            $validated = $this->validated($request);
            return $this->recurringPaymentService->createRecurring($validated['order_id'], $validated);
        } catch (Exception $exception) {
            throwNgeniusPackageResponse($exception);
        }
    }

    private function validated(array $request) {
        $validationRules = [
            'order_id' => 'required|string',
            'frequency' => 'required|in:DAILY,WEEKLY,MONTHLY,QUARTERLY,YEARLY',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after:start_date',
            'amount' => 'required|numeric|gt:0',
            'payment_token' => 'required|string',
            'description' => 'sometimes|string|max:255'
        ];

        $validator = Validator::make($request, $validationRules);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            throwNgeniusPackageResponse($error, [], 422);
        }

        $validated = $validator->validated();
        $validated['amount'] = $validated['amount'] * 100; // Convert to minor units

        return $validated;
    }
} 