<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminContext;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $school = AdminContext::school();

        return view('admin.dashboard', [
            'school' => $school,
            'counts' => [
                'posts' => $school->posts()->where('status', 'published')->count(),
                'pending' => $school->posts()->where('status', 'pending')->count(),
                'photos' => $school->galleries()->count(),
                'new_reg' => $school->registrations()->where('status', 'baru')->count(),
                'unread' => $school->messages()->where('is_read', false)->count(),
            ],
        ]);
    }

    /** Hanya super admin (dijaga middleware di rute). */
    public function switchSchool(Request $request)
    {
        $data = $request->validate(['school_id' => 'required|integer|exists:schools,id']);

        session(['admin_school_id' => (int) $data['school_id']]);

        return redirect()->route('admin.dashboard');
    }
}