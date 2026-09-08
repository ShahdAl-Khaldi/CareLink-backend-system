<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index()
    {
        // جلب كل المنشورات للأدمن (المقبولة والمعلقة)
        // لا تستخدم where('is_approved', true) هنا، لأن الأدمن يحتاج لرؤية الكل!
  //      $posts = Post::latest()->get();
$posts = Post::with('user')->latest()->get();
        return response()->json(['data' => $posts]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'image' => 'nullable|image|max:2048',
        ]);

        // كانت بتقرا $request->image_path (حقل الفرونت ما بيبعته إطلاقاً - بيبعت
        // ملف باسم image)، فصورة المنشور الجديد كانت دايماً بتنحفظ فاضية
        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('posts', 'public')
            : null;

        // نستخدم auth()->id() لجلب الـ user_id للمستخدم الذي سجل الدخول
        $post = Post::create([
            'title' => $request->title,
            'content' => $request->content,
            'image_path' => $imagePath,
            'user_id' => auth()->id(),
            'is_approved' => false, // نضمن أنها دائماً false عند الإضافة الجديدة
        ]);

        return response()->json(['message' => 'تم رفع المنشور بنجاح، بانتظار موافقة الإدارة'], 201);
    }

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        $data = $request->validate([
            'title' => 'required',
            'content' => 'required',
            'image' => 'nullable|image|max:2048',
        ]);
        unset($data['image']);

        // كان بيحط الملف بمفتاح 'image' واللي مش موجود بـ $fillable (العمود
        // الحقيقي اسمه image_path)، فالـ mass assignment كان بيرفضه بصمت
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('posts', 'public');
        }

        $post->update($data);
        return response()->json($post);
    }

    public function destroy($id)
    {
        Post::destroy($id);
        return response()->json(['message' => 'Deleted']);
    }

    public function approve($id)
    {
        $post = Post::findOrFail($id);

        // تحديث الحالة إلى true (مقبول)
        $post->update(['is_approved' => true]);

        return response()->json(['message' => 'تم قبول المنشور، أصبح الآن مرئياً للجمهور.']);
    }
}
