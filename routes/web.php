<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


// GLOBAL URL GOOGLE SCRIPT
$googleScriptUrl = "https://script.google.com/macros/s/AKfycbzEkMkE9sJItucdKwFHK7sYzpRORG5AL6Vwj6Xg98h3iuacMBeusVF7mqPJo8_8HvtaDQ/exec";


// ---- ROUTES ---- //

Route::get('/', function () use ($googleScriptUrl) {
    $response = Http::get($googleScriptUrl . "?action=list");
    $data = $response->json();
    return view('files', ['files' => $data['data'] ?? []]);
});

// Upload route
Route::post('/upload', function (Request $request) use ($googleScriptUrl) {

    $request->validate([
        'file' => 'required|file|max:50000'
    ]);

    $file = $request->file('file');

    $base64 = base64_encode(file_get_contents($file));

    $response = Http::post($googleScriptUrl, [
        'action' => 'upload',
        'file' => $base64,
        'filename' => $file->getClientOriginalName(),
        'mimeType' => $file->getClientMimeType()
    ]);

    return back()->with('result', $response->json());
});


// Get JSON list from Drive
Route::get('/files', function () use ($googleScriptUrl) {

    $response = Http::get($googleScriptUrl . "?action=list");

    return $response->json();
});


// Page to view files
Route::get('/drive-list', function () use ($googleScriptUrl) {

    $response = Http::get($googleScriptUrl . "?action=list");
    $data = $response->json();

    return view('files', ['files' => $data['data'] ?? []]);
});

Route::post('/file/rename', function (Request $request) use ($googleScriptUrl) {

    $response = Http::withHeaders([
        'Content-Type' => 'application/json'
    ])->post($googleScriptUrl, [
        'action'   => 'rename',
        'fileId'   => $request->file_id,
        'newName'  => $request->new_name
    ]);

    return back()->with('result', $response->json());
});


Route::post('/file/delete', function (Request $request) use ($googleScriptUrl) {

    $response = Http::withHeaders([
        'Content-Type' => 'application/json'
    ])->post($googleScriptUrl, [
        'action' => 'delete',
        'fileId' => $request->file_id
    ]);

    return back()->with('result', $response->json());
});
