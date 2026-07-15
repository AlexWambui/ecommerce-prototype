<?php

namespace Modules\CallbackMessage\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Exception;
use Modules\CallbackMessage\Models\CallbackMessage;
use Modules\CallbackMessage\Http\Resources\CallbackMessageResource;
use Modules\CallbackMessage\Http\Resources\CallbackMessageEditPageResource;

class CallbackMessageController extends Controller
{
    public function index()
    {
        return Inertia::render('guest/contactpage/Index');
    }

    public function list_callbacks(Request $request)
    {
        $callback_messages = CallbackMessage::query()
            ->search($request->search)
            ->resolved($request->status)
            ->latest()
            ->paginate(50);

        return inertia('app/callbacks/Index', [
            'callback_messages' => CallbackMessageResource::collection($callback_messages),
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone_number' => ['required', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:1000'],
        ], [
            'name.required' => 'Please fill in your full name',
            'phone_number.required' => 'Please fill in your phone number',
            'message.required' => 'Please fill in your message',
        ]);

        try {
            DB::beginTransaction();

            CallbackMessage::create([
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'message' => $request->message
            ]);

            DB::commit();

            Inertia::flash('toast', [
                'type' => "success",
                'message' => "Your message has been sent. We will contact you shortly!"
            ]);

            return to_route('callback-messages.index');
        } catch (Exception $e) {
            DB::rollback();

            Inertia::flash('toast', [
                'type' => "error",
                'message' => "Failed to save category: {$e->getMessage()}"
            ]);

            return back()->withInput();
        }
    }

    public function edit(CallbackMessage $callback_message)
    {
        if (!$callback_message->is_read) {
            $callback_message->update(['is_read' => true]);
        }

        return inertia('app/callbacks/Edit', [
            'callback_message' => new CallbackMessageEditPageResource($callback_message)
        ]);
    }

    public function toggleResolved(CallbackMessage $callback_message)
    {
        try {
            DB::beginTransaction();

            // Toggle the resolved status
            $callback_message->update([
                'is_resolved' => !$callback_message->is_resolved
            ]);

            DB::commit();

            $status = $callback_message->is_resolved ? 'resolved' : 'unresolved';
            
            session()->flash('toast', [
                'type' => 'success',
                'message' => "Callback message marked as {$status}!"
            ]);

            return back();
            
        } catch (Exception $e) {
            DB::rollback();

            session()->flash('toast', [
                'type' => 'error',
                'message' => "Failed to update status: {$e->getMessage()}"
            ]);

            return back();
        }
    }

    public function destroy(CallbackMessage $callback_message)
    {
        try {
            DB::beginTransaction();

            $callback_message->delete();

            DB::commit();

            session()->flash('toast', [
                'type' => 'success',
                'message' => 'Callback message deleted successfully!'
            ]);

            return to_route('callback-messages.list');
            
        } catch (Exception $e) {
            DB::rollback();

            session()->flash('toast', [
                'type' => 'error',
                'message' => "Failed to delete message: {$e->getMessage()}"
            ]);

            return back();
        }
    }
}