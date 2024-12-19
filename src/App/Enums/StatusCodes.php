<?php

namespace Jeybin\Networkintl\App\Enums;

/**
 * Status codes.
 *
 * @author Jeybin George
 * @since 2022-10-16
 */
final class StatusCodes{
    
    private static function list(): array
    {
        return [
            '00'=>'Transaction Success',
            '05'=>'Transaction Declined : Do not honor',
            '14'=>'Transaction Declined : Invalid account number',
            '41'=>'Transaction Declined : Lost Card',
            '43'=>'Transaction Declined : Stolen card',
            '51'=>'Transaction Declined : Insufficient funds',
            '54'=>'Transaction Declined : Expired card',
            '55'=>'Transaction Declined : Incorrect PIN',
            '99'=>'Transaction Failed : The payment gateway could not authenticate the transaction fingerprint.'
        ];
    }


    public static function find(string $search, string $by = 'code'): ?array
    {
        $statusCodes = self::list();
        if ($by === 'code') {
            return isset($statusCodes[$search]) ? ['code' => $search, 'message' => $statusCodes[$search]] : null;
        }
        
        $responseKey = array_search($search, array_values($statusCodes));
        if ($responseKey === false) {
            return null;
        }
        return ['code' => array_keys($statusCodes)[$responseKey], 'message' => array_values($statusCodes)[$responseKey]];
    }

}
