<?php
namespace App\Http\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ValidationHelper {

    // Checking for validation errors
    // Request = Request object
    // Rules = Validation rules
    public static function validate(Request $request, array $rules)
    {
        $validator = Validator::make($request->all(), $rules);
        return $validator->fails() ? response()->json(['Validation Error' => $validator->errors()], 422) : null;
    }
}
