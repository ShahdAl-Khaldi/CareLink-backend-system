<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        return response()->json(['data' => Faq::latest()->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'category' => 'nullable|string|max:100',
            'status' => 'nullable|in:published,draft',
        ]);

        $faq = Faq::create($validated);

        return response()->json(['message' => 'تمت إضافة السؤال', 'data' => $faq], 201);
    }

    public function update(Request $request, $id)
    {
        $faq = Faq::findOrFail($id);

        $validated = $request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'category' => 'nullable|string|max:100',
            'status' => 'nullable|in:published,draft',
        ]);

        $faq->update($validated);

        return response()->json(['message' => 'تم تحديث السؤال', 'data' => $faq]);
    }

    public function destroy($id)
    {
        Faq::destroy($id);

        return response()->json(['message' => 'تم حذف السؤال']);
    }
}
