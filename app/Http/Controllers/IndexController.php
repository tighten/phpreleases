<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class IndexController
{
    public function index()
    {
        return view('index', ['graphicUpdatedToday' => $this->graphicHasBeenUpdatedToday()]);
    }

    public function graphicHasBeenUpdatedToday(): bool
    {
        // check if the graphic exists and has been updated today
        return Storage::disk('public')->exists('supported-versions.svg') && Carbon::now()->subDay()->timestamp < Storage::disk('public')->lastModified('supported-versions.svg');
    }
}
