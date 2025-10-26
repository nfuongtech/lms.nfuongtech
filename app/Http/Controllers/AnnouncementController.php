<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function show(string $slug)
    {
        $post = Announcement::query()->where('slug', $slug)->firstOrFail();

        // Nếu có redirect_url thì chuyển hướng
        if ($post->redirect_url) {
            return redirect()->away($post->redirect_url);
        }

        return view('announcements.show', [
            'post' => $post,
            'others' => Announcement::active()->whereKeyNot($post->id)->latest('publish_at')->take(6)->get(),
        ]);
    }
}
