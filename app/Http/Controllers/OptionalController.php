<?php

namespace App\Http\Controllers;

use App\Models\Optional;
use Illuminate\Http\Request;

class OptionalController extends Controller
{
    public function single(Request $request)
    {
        if( isset($request->feature_id) ) {
            return Optional::find($request->feature_id);
        }
        return null;
    }
}
