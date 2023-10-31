<?php
function calculateEarnings($getRecord)
{
    $prescribedDrugs = $getRecord()->prescribedDrugs->load('drugs');

    $totalEarnings = 0;

    foreach ($prescribedDrugs as $prescribedDrug) {
        $drug = $prescribedDrug->drugs;

        $incomeForDrug = $drug->price * $prescribedDrug->quantity;
        $totalEarnings += $incomeForDrug;
    }

    return number_format($totalEarnings, 0, ',', '.');
}

