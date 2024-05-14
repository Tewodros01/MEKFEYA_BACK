<?php

namespace App\Http\Controllers\ThermalPrinters;

use App\Http\Controllers\Controller;

class ReceiptThermalPrintersController extends Controller
{
    public function printReceipt()
    {
        // Path to your script (adjust to your environment)
        $scriptPath = __DIR__ . '/print-script.php';

        // Execute the script to send the print job
        exec("php $scriptPath", $output, $return_var);

        if ($return_var === 0) {
            return response("Printed successfully!", 200);
        } else {
            return response("Failed to print. Error code: " . $return_var, 500);
        }
    }
}
