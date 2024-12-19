<?php

namespace Jeybin\Networkintl\App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Validator;
use Jeybin\Networkintl\App\Services\PaymentLinkService;

final class PaymentLinkController {
    
    private $paymentLinkService;

    public function __construct() {
        $this->paymentLinkService = new PaymentLinkService();
    }

    public function create(array $request) {
        try {
            $validated = $this->validated($request);
            return $this->paymentLinkService->createPaymentLink($validated);
        } catch (Exception $exception) {
            throwNgeniusPackageResponse($exception);
        }
    }

    private function validated(array $request) {
        $validationRules = [
            'amount' => 'required|numeric|gt:0',
            'expiry_date' => 'required|date_format:Y-m-d|after:today',
            'order_reference' => 'required|string|max:50',
            'description' => 'sometimes|string|max:255',
            'payment_methods' => 'sometimes|array',
            'payment_methods.*' => 'string|in:CARD,APPLE_PAY,GOOGLE_PAY',
            'language' => 'sometimes|in:en,ar,fr'
        ];

        $validator = Validator::make($request, $validationRules);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            throwNgeniusPackageResponse($error, [], 422);
        }

        $validated = $validator->validated();
        $validated['amount'] = $validated['amount'] * 100; // Convert to minor units
        $validated['language'] = $validated['language'] ?? 'en';

        return $validated;
    }
} 