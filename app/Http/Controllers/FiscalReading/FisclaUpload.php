<?php

namespace App\Http\Controllers\FiscalReading;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FisclaUpload extends Controller
{
    //
    public function upload(Request $request)
    {
        //return $request->file('file');
        try {
            $validation = validator($request->all(), [
                'file' => 'required|file|mimetypes:application/json,text/plain|max:10240', // Adjust file types and size as needed
            ]);
            if ($validation->fails()) {
                $error = $validation->errors()->getMessages();
                return response(['errorMessage' => $error], 417);
            }
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('ejuploads'), $fileName);

            return response()->json([
                'message' => 'File uploaded successfully',
                'data' => '../../ejuploads/' . $fileName
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => "Error uploading file",
                'error-message' => $th,
            ], 500);
        }
    }
}
