<?php
namespace App\Http\Controllers\ThermalPrinters;

require __DIR__ . '/../../../../vendor/autoload.php';

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

try {
    $connector = new WindowsPrintConnector("COM5");
    $printer = new Printer($connector);

    $printer->text("Hello, Aclas PP7X!\n");
    $printer->cut();

    echo "Printed successfully!";
} catch (Exception $e) {
    error_log("Error while printing: " . $e->getMessage());
    echo "Error while printing: " . $e->getMessage();
} finally {
    if (isset($printer)) {
        $printer->close();
    }
}
