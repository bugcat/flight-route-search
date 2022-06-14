<?php
namespace App\Support;

class Collection extends \Illuminate\Support\Collection
{

    /**
     * Key an associative array by a field or using a callback.
     *
     * @param  (callable(TValue, TKey): array-key)|array|string  $keyBy
     * @return static<array-key, TValue>
     */
    public function keyByMultiple($keyBy)
    {
        $keyBy = $this->valueRetriever($keyBy);

        $results = [];

        foreach ($this->items as $key => $item) {
            $resolvedKey = $keyBy($item, $key);

            if (is_object($resolvedKey)) {
                $resolvedKey = (string) $resolvedKey;
            }

            $results[$resolvedKey][] = $item;
        }

        return new static($results);
    }

}
