<?php

namespace App\Http\Controllers\Api;

use App\Services\TranslationService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TranslationExportController extends Controller
{
    private $translations;

    public function __construct(TranslationService $translations)
    {
        $this->translations = $translations;
    }

    public function show(Request $request, $locale)
    {
        $translations = $this->translations->export($locale, $request->only(['tag']));

        return response()->json([
            'locale' => $locale,
            'count' => count($translations),
            'translations' => $translations,
        ]);
    }
}
