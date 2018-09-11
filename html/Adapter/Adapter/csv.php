<?php


class csv
{

    function create($path, $content, $delimiter = null)
    {
        $count = 0;
        $handle = fopen($path, 'w');
        foreach ($content[array_keys($content)[0]] as $key => $item) {
            $count++;
            if ($count < sizeof($content[array_keys($content)[0]])) {
                $out = $key . $delimiter;
            } else {
                $out = $key . "\n";
                $count = 0;
            }
            fwrite($handle, $out);
        }
        foreach ($content as $fields) {
            fputcsv($handle, $fields, $delimiter);
        }
        return $handle;
    }

    function read($path, $delimiter = null)
    {
        $output_array = [];
        $count = 0;

        $csvfile = file($path);
        $header = str_getcsv($csvfile[0], $delimiter);
        foreach ($csvfile as $index) {
            if ($count > 0) {
                $linearr = str_getcsv($index, $delimiter);
                $output_array[$count] = array_combine($header, $linearr);
            }
            $count++;
        }
        foreach ($output_array as $item => $unused) {
            foreach ($output_array[$item] as $key => $value) {
                if ($key == "") {
                    unset($output_array[$item][$key]);
                }
            }
        }
        return $output_array;
    }

    public function combinearrays($array1, $array2)
    {
        $output_array = [];
        $header = array_flip(array_unique(array_merge(array_keys($array1[1]), array_keys($array2[1]))));

        for ($i = 1; $i <= count($array1) + count($array2); $i++) {
            $output_array[$i] = $header;
        }
        foreach ($output_array as $id => $item) {
            foreach ($item as $key => $value) {

                if ($id <= count($array1)) {
                    if (array_key_exists($key, $array1[$id])) {
                        $output_array[$id][$key] = $array1[$id][$key];
                    } else {
                        $output_array[$id][$key] = "";
                    }
                } else {
                    if (array_key_exists($key, $array2[$id - count($array1)])) {
                        $output_array[$id][$key] = $array2[$id - count($array1)][$key];
                    } else {
                        $output_array[$id][$key] = "";
                    }
                }
            }
        }
        return ($output_array);
    }
}