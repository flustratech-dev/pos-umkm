<?php

namespace App\Services\Payment;

class QrisService
{
    /**
     * Convert static QRIS payload to dynamic QRIS payload by injecting the amount.
     */
    public function convertToDynamic(string $staticPayload, float $amount): string
    {
        // 1. Clean the static payload
        $staticPayload = trim($staticPayload);

        // 2. Parse the TLV (Tag-Length-Value) data
        $tags = $this->parseTLV($staticPayload);

        // 3. Update Tag 01 (Point of Initiation Method) to "12" (Dynamic)
        $tags['01'] = '12';

        // 4. Update/Insert Tag 54 (Transaction Amount)
        // Amount must be formatted without decimals for Indonesian Rupiah
        $amountInt = (int) round($amount);
        $tags['54'] = (string) $amountInt;

        // 5. Remove Tag 63 (CRC) if it exists, as we will recalculate it
        unset($tags['63']);

        // 6. Rebuild the TLV string (sorted by tag key numerically/alphabetically)
        ksort($tags);
        $rebuilt = $this->buildTLV($tags);

        // 7. Append Tag 63 with length "04"
        $rebuilt .= '6304';

        // 8. Calculate CRC16 of the rebuilt string
        $crc = $this->calculateCRC16($rebuilt);

        // 9. Return full dynamic QRIS payload
        return $rebuilt . $crc;
    }

    /**
     * Parse TLV string into associative array of tag => value
     */
    private function parseTLV(string $payload): array
    {
        $tags = [];
        $i = 0;
        $len = strlen($payload);

        while ($i < $len) {
            // Tag is 2 characters
            $tag = substr($payload, $i, 2);
            if (strlen($tag) < 2) {
                break;
            }

            // Length is 2 characters
            $lengthStr = substr($payload, $i + 2, 2);
            if (strlen($lengthStr) < 2) {
                break;
            }

            $length = (int) $lengthStr;

            // Value is $length characters
            $value = substr($payload, $i + 4, $length);

            $tags[$tag] = $value;
            $i += 4 + $length;
        }

        return $tags;
    }

    /**
     * Build TLV string from associative array
     */
    private function buildTLV(array $tags): string
    {
        $payload = '';
        foreach ($tags as $tag => $value) {
            $length = str_pad((string) strlen($value), 2, '0', STR_PAD_LEFT);
            $payload .= $tag . $length . $value;
        }
        return $payload;
    }

    /**
     * Calculate CRC16-CCITT-FALSE
     */
    private function calculateCRC16(string $data): string
    {
        $crc = 0xFFFF;
        $polynomial = 0x1021;

        $len = strlen($data);
        for ($i = 0; $i < $len; $i++) {
            $crc ^= (ord($data[$i]) << 8);
            for ($j = 0; $j < 8; $j++) {
                if (($crc <<= 1) & 0x10000) {
                    $crc ^= $polynomial;
                }
                $crc &= 0xFFFF;
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }
}
