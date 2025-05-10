<?php

declare(strict_types=1);

namespace SzepeViktor\WordPress\QueryMonitor\Ajax;

use QM_Data;

class RequestCollector extends \QM_DataCollector
{
    public $id = 'request_vars';

    public function get_storage(): QM_Data
    {
        return new RequestData();
    }

    public function process(): void
    {
        $this->data->request_variables = array_merge(
            $this->dot($_GET, 'GET.'),
            $this->dot($_POST, 'POST.')
        );
    }

    /**
     * @see https://github.com/laravel/framework/blob/12.x/src/Illuminate/Collections/Arr.php#L111
     */
    protected function dot(array $array, string $prepend = ''): array
    {
        $results = [];

        $flatten = function ($data, $prefix) use (&$results, &$flatten): void {
            foreach ($data as $key => $value) {
                $newKey = $prefix.$key;

                if (is_array($value) && ! empty($value)) {
                    $flatten($value, $newKey.'.');
                } else {
                    $results[$newKey] = $value;
                }
            }
        };

        $flatten($array, $prepend);

        return $results;
    }
}
