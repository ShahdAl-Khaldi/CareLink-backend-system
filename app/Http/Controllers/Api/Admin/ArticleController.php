<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Article;
class ArticleController extends Controller
{
    public function index() {
    return response()->json(['data' => Article::latest()->get()]);
}

public function store(Request $request) {
    $data = $request->validate([
        'title' => 'required',
        'category' => 'required',
        'author' => 'required',
        'excerpt' => 'required',
        'content' => 'nullable|string',
        'status' => 'required',
        'featured' => 'nullable|boolean',
        'image' => 'nullable|image|max:2048',
    ]);

    if ($request->hasFile('image')) {
        $data['image'] = $request->file('image')->store('articles', 'public');
    }

    return response()->json(Article::create($data), 201);
}

public function update(Request $request, $id)
{
    $article = Article::findOrFail($id);

    // ضع قواعد التحقق هنا داخل المصفوفة
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'category' => 'required|string|max:100',
        'author' => 'required|string|max:100',
        'excerpt' => 'required|string',
        'content' => 'nullable|string',
        'status' => 'required|in:published,draft',
        'featured' => 'nullable|boolean',
        'image' => 'nullable|image|max:2048',
    ]);

    if ($request->hasFile('image')) {
        $validated['image'] = $request->file('image')->store('articles', 'public');
    } else {
        unset($validated['image']);
    }

    $article->update($validated);

    return response()->json($article);
}
public function destroy($id) {
    Article::destroy($id);
    return response()->json(['message' => 'Deleted']);
}
}
