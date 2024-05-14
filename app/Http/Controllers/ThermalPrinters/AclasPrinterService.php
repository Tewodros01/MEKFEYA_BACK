<?php

namespace App\Http\Controllers\ThermalPrinters;

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintBuffers;

class AclasPrinterService
{
    protected $printer;

    public function __construct()
    {
        $config = config('printer');

        // Create a SerialPrintConnector object
        $connector = new SerialPrintConnector($config['port'], $config['baud_rate']);

        $this->printer = new Printer($connector, $config['profile']); // Optional profile if needed

        $this->printer->setPrintBuffer(new PrintBuffers()); // Optional for faster printing
    }

    public function printText($text)
    {
        $this->printer->text($text . PHP_EOL); // Add newline for readability
    }

    public function printBarcode($type, $data)
    {
        $this->printer->barcode($data, $type);
    }

    public function cutPaper()
    {
        $this->printer->cut();
    }

    // ... Add other printing methods as needed (e.g., for images, custom formatting)

    public function closeConnection()
    {
        $this->printer->close();
    }

    // ... Destructor to close connection automatically (optional)
}
