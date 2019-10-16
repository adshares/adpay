<?php declare(strict_types = 1);

namespace Adshares\AdPay\Lib;

final class InfoHelper
{
    /** @param array<string> $data */
    public static function formatTxt(array $data): string
    {
        $response = '';
        foreach ($data as $key => $value) {
            $key = strtoupper(preg_replace('([A-Z])', '_$0', $key));
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            if (strpos((string)$value, ' ') !== false) {
                $value = '"'.$value.'"';
            }
            $response .= sprintf("%s=%s\n", $key, $value);
        }

        return $response;
    }
}
