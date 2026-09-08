<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index()
    {
        return response()->json(['data' => Testimonial::latest()->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:100',
            'content' => 'required|string',
            'doctor_id' => 'nullable|exists:users,id',
            'doctor_name' => 'nullable|string|max:255',
            'rating' => 'nullable|integer|min:1|max:5',
            'status' => 'nullable|in:published,draft',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('testimonials', 'public');
        }

        $testimonial = Testimonial::create($validated);

        return response()->json(['message' => 'تمت إضافة الرأي', 'data' => $testimonial], 201);
    }

    public function update(Request $request, $id)
    {
        $testimonial = Testimonial::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:100',
            'content' => 'required|string',
            'doctor_id' => 'nullable|exists:users,id',
            'doctor_name' => 'nullable|string|max:255',
            'rating' => 'nullable|integer|min:1|max:5',
            'status' => 'nullable|in:published,draft',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('testimonials', 'public');
        } else {
            unset($validated['image']);
        }

        $testimonial->update($validated);

        return response()->json(['message' => 'تم تحديث الرأي', 'data' => $testimonial]);
    }

    public function destroy($id)
    {
        Testimonial::destroy($id);

        return response()->json(['message' => 'تم حذف الرأي']);
    }
}
