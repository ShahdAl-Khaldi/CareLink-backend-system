<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;

class NewsletterSubscriberController extends Controller
{
    public function index()
    {
        return response()->json(['data' => NewsletterSubscriber::latest()->get()]);
    }

    public function destroy($id)
    {
        NewsletterSubscriber::destroy($id);

        return response()->json(['message' => 'تم حذف المشترك']);
    }
}
