<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ContactMessageReply;
use App\Models\ContactMessage;
use App\Support\AdminContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MessageController extends Controller
{
    public function index()
    {
        $messages = ContactMessage::with('school')
            ->where('school_id', AdminContext::schoolId())
            ->latest()->paginate(20);

        return view('admin.messages', ['messages' => $messages]);
    }

    public function read(int $id)
    {
        $this->find($id)->update(['is_read' => true]);

        return back();
    }

    public function reply(Request $request, int $id)
    {
        $message = $this->find($id);

        $data = $request->validate([
            'reply' => 'required|string|max:5000',
        ]);

        if (blank($message->email)) {
            return back()->withErrors(['reply' => 'Pengirim tidak memiliki email, balasan tidak dapat dikirim.']);
        }

        $message->fill([
            'reply' => $data['reply'],
            'replied_at' => now(),
            'replied_by' => $request->user()->id,
            'is_read' => true,
        ]);

        try {
            Mail::to($message->email)->send(new ContactMessageReply($message, $message->school));
        } catch (\Throwable $e) {
            report($e);

            // Jawaban belum disimpan agar bisa dicoba lagi
            return back()->withErrors(['reply' => 'Email gagal terkirim. Periksa pengaturan email lalu coba lagi.'])->withInput();
        }

        $message->save();

        return back()->with('status', 'Balasan terkirim ke '.$message->email.'.');
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