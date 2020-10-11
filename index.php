<?php

require_once './Utils.php';

if (isset($argv[1]) && isset($argv[2])) {

    $csv = $argv[1];
    $json = $argv[2];

    $products = Utils::parseCSV($csv);
    $categories = Utils::parseJSON($json)["categories"];
    $benefitsOfCategories = array();

    for ($i = 0; $i < count($products); $i++) { // Iterate over all the products
        $product = $products[$i];
        $quantity = Utils::stringToFloat($product["QUANTITY"], FALSE);
        $cost = Utils::stringToFloat($product["COST"], TRUE);
        $hasCategory = FALSE;

        // I'm creating an array with categories that I find in products, it is where I will put total benefitsOfCategories.
        if (array_key_exists($product["CATEGORY"], $benefitsOfCategories) == FALSE)
            $benefitsOfCategories[$product["CATEGORY"]] = 0;

        foreach ($categories as $categoryName => $formula) { // Iterate over all the categories

            if (strtolower($categoryName) == strtolower($product["CATEGORY"])) {

                // Calc final PVP of the product
                $pvp = Utils::calcPVP($formula, $cost);

                // Calc benefit of category and accumulate into benefitsOfCategories 
                $benefitsOfCategories[$product["CATEGORY"]]
                    += Utils::calcBenefit($quantity, $cost, $pvp);

                // Utils::printProduct($product["PRODUCT"], $categoryName, $quantity, $cost, $formula, $pvp); // To print product info

                $hasCategory = TRUE;
            }
        }
        if (!$hasCategory) { // Apply generic category

            // Create a new category and put into benefitsOfCategories
            $benefitsOfCategories[$product["CATEGORY"]] = 0;

            // Calc final PVP of the product
            $pvp = Utils::calcPVP($formula, $cost);

            // Calc benefit of category and accumulate it into benefitsOfCategories 
            $benefitsOfCategories[$product["CATEGORY"]]
                += Utils::calcBenefit($quantity, $cost, $pvp);

            // Utils::printProduct($product["PRODUCT"], $categoryName, $quantity, $cost, $formula, $pvp); // To print product info
        }
    }
    foreach ($benefitsOfCategories as $categoryName => $benefit) {
        echo $categoryName . ": " . number_format($benefit, 2, '.', '') . "\n";
    }
} else {
    throw new Exception("Parameters are wrong");
}

