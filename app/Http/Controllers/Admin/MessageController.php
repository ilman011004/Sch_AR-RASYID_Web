<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Support\AdminContext;

class MessageController extends Controller
{
    public function index()
    {
        $messages = ContactMessage::where('school_id', AdminContext::schoolId())
            ->latest()->paginate(20);

        return view('admin.messages', ['messages' => $messages]);
    }

    public function read(int $id)
    {
        $this->find($id)->update(['is_read' => true]);

        return back();
    }

    public function destroy(int $id)
    {
        $this->find($id)->delete();

        return back()->with('status', 'Pesan dihapus.');
    }

    private function find(int $id): ContactMessage
    {
        return ContactMessage::where('school_id', AdminContext::schoolId())->findOrFail($id);
    }
}