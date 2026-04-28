<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreTranslationRequest;
use App\Http\Requests\UpdateTranslationRequest;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TranslationController extends Controller
{
    private $translations;

    public function __construct(TranslationService $translations)
    {
        $this->translations = $translations;
    }

    public function index(Request $request)
    {
        return response()->json($this->translations->search(
            $request->only(['q', 'locale', 'key', 'tag']),
            $request->input('per_page', 50)
        ));
    }

    public function store(StoreTranslationRequest $request)
    {
        return response()->json([
            'data' => $this->translations->store($request->validated()),
        ], 201);
    }

    public function show($id)
    {
        return response()->json([
            'data' => $this->translations->find($id),
        ]);
    }

    public function update(UpdateTranslationRequest $request, $id)
    {
        return response()->json([
            'data' => $this->translations->update($id, $request->validated()),
        ]);
    }

    public function destroy($id)
    {
        $this->translations->delete($id);

        return response()->json(null, 204);
    }
}
