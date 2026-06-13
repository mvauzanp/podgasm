<?php

namespace App\Http\Controllers;

use App\Models\CsMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CsChatController extends Controller
{
    /**
     * User Side: Fetch messages for the authenticated client (B2C/B2B Reseller)
     * Pass ?mark_read=1 to mark admin replies as read (only when chat is open)
     */
    public function fetchMessages(Request $request)
    {
        $user = Auth::user();
        if (!$this->isAllowedClient($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Only mark admin replies as read when explicitly requested (chat window is open)
        if ($request->query('mark_read') == '1') {
            CsMessage::where('user_id', $user->id)
                ->where('sender_id', '!=', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        $messages = CsMessage::where('user_id', $user->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'messages' => $messages
        ]);
    }

    /**
     * User Side: Send message to CS (Admin)
     */
    public function sendMessage(Request $request)
    {
        $user = Auth::user();
        if (!$this->isAllowedClient($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $message = CsMessage::create([
            'user_id' => $user->id,
            'sender_id' => $user->id,
            'message' => trim($request->message),
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Admin Side: Show backoffice panel
     */
    public function adminIndex()
    {
        return view('pages.admin.cs-chat.index');
    }

    /**
     * Admin Side: Fetch active chat threads grouped by user
     */
    public function adminFetchThreads()
    {
        // Fetch users who have messages, sorted by latest message
        $threads = CsMessage::select('user_id', DB::raw('MAX(created_at) as latest_message_time'))
            ->groupBy('user_id')
            ->orderBy('latest_message_time', 'desc')
            ->get();

        if ($threads->isEmpty()) {
            return response()->json([
                'success' => true,
                'threads' => []
            ]);
        }

        // Fetch all user details in a single query to avoid N+1
        $userIds = $threads->pluck('user_id')->toArray();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        // Fetch unread counts in a single query grouped by user_id
        $unreadCounts = CsMessage::select('user_id', DB::raw('COUNT(*) as aggregate'))
            ->whereColumn('sender_id', 'user_id')
            ->where('is_read', false)
            ->whereIn('user_id', $userIds)
            ->groupBy('user_id')
            ->get()
            ->pluck('aggregate', 'user_id');

        // Fetch the latest message content for each user efficiently
        $latestMessages = CsMessage::whereIn('id', function ($query) use ($userIds) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('cs_messages')
                    ->whereIn('user_id', $userIds)
                    ->groupBy('user_id');
            })
            ->get()
            ->keyBy('user_id');

        $threadList = [];
        foreach ($threads as $t) {
            $client = $users->get($t->user_id);
            if ($client) {
                $unreadCount = $unreadCounts->get($client->id, 0);
                $latestMsg = $latestMessages->get($client->id);

                // Determine B2B reseller or B2C customer tag
                $tag = ($client->role === 'branch' && $client->b2b_type === 'reseller') ? 'B2B Reseller' : 'B2C Customer';
                $badgeClass = ($client->role === 'branch' && $client->b2b_type === 'reseller') ? 'bg-primary' : 'bg-success';

                $threadList[] = [
                    'user_id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                    'tag' => $tag,
                    'badge_class' => $badgeClass,
                    'unread_count' => $unreadCount,
                    'latest_message' => $latestMsg ? Str::limit($latestMsg->message, 40) : '',
                    'time_formatted' => $t->latest_message_time ? date('H:i', strtotime($t->latest_message_time)) : ''
                ];
            }
        }

        return response()->json([
            'success' => true,
            'threads' => $threadList
        ]);
    }

    /**
     * Admin Side: Fetch all messages in a specific user's thread
     */
    public function adminFetchMessages($userId)
    {
        $client = User::findOrFail($userId);

        // Mark client's messages as read by admin
        CsMessage::where('user_id', $client->id)
            ->where('sender_id', $client->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = CsMessage::where('user_id', $client->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone ?? '-',
                'address' => $client->address ?? '-',
                'tag' => ($client->role === 'branch' && $client->b2b_type === 'reseller') ? 'B2B Reseller' : 'B2C Customer'
            ],
            'messages' => $messages
        ]);
    }

    /**
     * Admin Side: Send a reply to a user thread
     */
    public function adminSendMessage(Request $request, $userId)
    {
        $client = User::findOrFail($userId);

        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $message = CsMessage::create([
            'user_id' => $client->id,
            'sender_id' => Auth::id(), // Admin
            'message' => trim($request->message),
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Helper to verify if user is B2C Customer or B2B Reseller
     */
    private function isAllowedClient($user)
    {
        if (!$user) return false;
        
        // Allowed: B2C Customer (role customer)
        if ($user->role === 'customer') {
            return true;
        }

        // Allowed: B2B Reseller (role branch and b2b_type reseller)
        if ($user->role === 'branch' && $user->b2b_type === 'reseller') {
            return true;
        }

        // Disallowed: Admin and Cabang/Branch (role branch and b2b_type branch)
        return false;
    }
}
