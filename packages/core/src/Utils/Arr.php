<?php

namespace Lunar\Utils;

use TreeWalker;

class Arr
{
    public static function diff($source, $incoming)
    {
        return json_decode(
            (new TreeWalker([]))->getDiff($source, $incoming)
        );
    }

    public static function permutate(array $setTuples, $isRecursiveStep = false)
    {
        $countTuples = count($setTuples);

        if (1 === $countTuples) {
            $result = [];
            $key = array_key_first($setTuples);

            foreach ($setTuples[$key] as $value) {
                $result[] = [$key => $value];
            }

            return $result;
        }

        if (0 === $countTuples) {
            return [];
        }

        foreach ($setTuples as $tuple) {
            if (! is_array($tuple)) {
                throw new \InvalidArgumentException('The set builder requires a single array of one or more array sets.');
            }
        }

        $keys = array_keys($setTuples);
        $a = array_shift($setTuples);
        $k = array_shift($keys);

        $b = self::permutate($setTuples, true);

        $result = [];

        foreach ($a as $valueA) {
            if ($valueA) {
                foreach ($b as $valueB) {
                    if ($isRecursiveStep) {
                        $result[] = array_merge([$valueA], (array) $valueB);
                    } else {
                        $result[] = [$k => $valueA] + array_combine($keys, (array) $valueB);
                    }
                }
            }
        }

        return $result;
    }
}
