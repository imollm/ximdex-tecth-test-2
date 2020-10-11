<?php

class Utils
{
    public static function getFileHandler($fileName)
    {
        try {
            return fopen($fileName, "r");
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public static function parseCSV($csvFileName)
    {
        $fileHandler = Utils::getFileHandler($csvFileName);

        $keys = fgetcsv($fileHandler, 0, ";");

        while (($row = fgetcsv($fileHandler, 0, ";")) !== FALSE) {
            $products[] = array_combine($keys, $row);
        }
        fclose($fileHandler);
        return $products;
    }
    public static function parseJSON($jsonFileName)
    {
        $fileHandler = Utils::getFileHandler($jsonFileName);
        $arrayProducts = json_decode(stream_get_contents($fileHandler), true);
        fclose($fileHandler);
        return $arrayProducts;
    }
    public static function calcPVP($formula, $cost)
    {
        $patternFixedFirst = "/^[\+\-][0-9\.]+€/";
        $patternPercentFirst = "/^[\+\-][0-9\.]+%/";
        $patternFixedEnd = "/[\+\-][0-9\.]+€$/";
        $patternPercentEnd = "/[\+\-][0-9\.]+%$/";

        $patterns = array(
            "fixed0" => $patternFixedFirst,
            "percent1" => $patternPercentFirst,
            "fixed2" => $patternFixedEnd,
            "percent3" => $patternPercentEnd
        );

        $fixedAlreadyApplied = FALSE;
        $percentAlreadyApplied = FALSE;

        $pvp = 0;

        foreach ($patterns as $type => $pattern) {

            if (preg_match($pattern, $formula, $match) == 1) {
                $onlyNumber = preg_replace("/[^\+\-0-9\.]/", "", $match);
                $valueOfFee = floatval($onlyNumber[0]);

                if (strpos($type, "fixed") !== FALSE && !$fixedAlreadyApplied) {
                    $pvp = ($pvp == 0) ? $cost + $valueOfFee : $pvp + $valueOfFee;
                    $fixedAlreadyApplied = TRUE;
                }
                if (strpos($type, "percent") !== FALSE && !$percentAlreadyApplied) {
                    $pvp = ($pvp == 0) ? $cost + ($cost * ($valueOfFee / 100)) : $pvp + ($pvp * ($valueOfFee / 100));
                    $percentAlreadyApplied = TRUE;
                }
            }
        }
        return $pvp;
    }
    public static function stringToFloat($value, $isCost)
    {
        return ($isCost) ?
            floatval(preg_replace("/[^0-9\.\,]/", "", str_replace(',', '.', str_replace('.', '', $value)))) :
            floatval(str_replace(',', '.', str_replace('.', '', $value)));
    }
    public static function calcBenefit($quantity, $cost, $pvp)
    {
        $totalCost = Utils::stringToFloat($quantity, FALSE) * $cost;
        $totalSale = Utils::stringToFloat($quantity, FALSE) * $pvp;
        return $totalSale - $totalCost;
    }
    public static function printProduct($product, $categoryName, $quantity, $cost, $formula, $pvp)
    {
        echo    "Product: " . $product . "\n" .
            "Category: " . $categoryName . "\n" .
            "Quantity: " . $quantity . "\n" .
            "Cost: " . $cost . "\n" .
            "Formula: " . $formula . "\n" .
            "PVP: " . $pvp . "\n" .
            "Total cost: " . $cost * $quantity . "\n" .
            "Total saled: " . $pvp * $quantity . "\n" .
            "Benefit: " . ($pvp - $cost) * $quantity . "\n\n";
    }
}
