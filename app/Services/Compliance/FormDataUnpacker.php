<?php

namespace App\Services\Compliance;

class FormDataUnpacker
{
    public static function unpack(array $data): array
    {
        $viewData = [
            'header' => $data['header'] ?? [],
            'rows' => $data['rows'] ?? [],
            'totals' => $data['totals'] ?? [],
        ];

        foreach ($data as $key => $value) {
            if (!in_array($key, ['header', 'rows', 'totals'])) {
                $viewData[$key] = $value;
            }
        }

        return $viewData;
    }
}
