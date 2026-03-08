<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification; // Or whatever notification class you are querying
use Illuminate\Support\Facades\Auth; // For getting the authenticated user
use Illuminate\Support\Facades\Log; // For logging errors
use App\Models\User; // Assuming you have a User model for notifications
use Illuminate\Support\Facades\Notification; // For sending notifications
use App\Notifications\NewConsultationRequest; // Example notification class, adjust as needed
use App\Models\Consultation;
use Illuminate\Support\Facades\DB; // For database transactions
use Illuminate\Support\Facades\Http; // For sending HTTP requests to FCM



class NotificationController extends Controller
{
    /**
     * Display a listing of notifications for the web dashboard (e.g., Doctor's panel).
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get the currently authenticated user (expected to be a doctor/medecin)
        $user = Auth::user();

        // Fetch paginated notifications for the user
        // You might want to fetch only unread notifications here, or all and filter in Blade
        $notifications = $user->notifications()->latest()->paginate(10); // Fetch all, latest first

        return view('medecin.notifications.index', compact('notifications'));
    }

     public function markAsRead(Request $request) // <-- Changed: Only Request $request here
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            // Get the notification ID from the request input
            $notificationId = $request->input('notification_id'); // <-- Get ID from input

            if (empty($notificationId)) {
                return response()->json(['success' => false, 'message' => 'Notification ID is missing'], 400);
            }

            // Find the notification belonging to the user and ensure it's unread
            $notification = $user->unreadNotifications()->where('id', $notificationId)->first();

            if ($notification) {
                $notification->markAsRead(); 
            }


        } catch (\Exception $e) {
            Log::error('Failed to mark specific notification as read (web): ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }

    /**
     * Mark a specific notification as read from the web dashboard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
   

    /**
     * Mark all unread notifications as read for the web dashboard.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $user->unreadNotifications->markAsRead(); // Mark all unread notifications for the user as read

            return response()->json(['success' => true, 'message' => 'All notifications marked as read']);

        } catch (\Exception $e) {
            Log::error('Failed to mark all notifications as read (web): ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }

    /**
     * Get authenticated user's notifications for API consumption (Flutter app).
     * This method will return JSON.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
  public function getApiNotifications(Request $request)
{
    DB::beginTransaction();
    try {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: User not authenticated'
            ], 401);
        }

        // Eager load any relationships if needed
        $notifications = $user->notifications()
            ->with(['notifiable']) // Example if you need related data
            ->latest()
            ->get();

        // Transform notifications
        $formattedNotifications = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => $this->getNotificationType($notification),
                'data' => $this->formatNotificationData($notification),
                'read_at' => $notification->read_at?->toDateTimeString(),
                'created_at' => $notification->created_at->toDateTimeString(),
                'is_read' => $notification->read_at !== null,
            ];
        });

        DB::commit();

        return response()->json([
            'success' => true,
            'notifications' => $formattedNotifications,
            'total' => $notifications->count(),
            'unread_count' => $user->unreadNotifications()->count(),
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Notification API Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve notifications',
            'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
        ], 500);
    }
}

// Helper method to get clean notification type
protected function getNotificationType($notification)
{
    $type = class_basename($notification->type);
    return str_replace('Notification', '', $type); // Removes 'Notification' suffix if present
}

// Helper method to format notification data consistently
protected function formatNotificationData($notification)
{
    $data = $notification->data;
    
    // Ensure all notifications have these basic fields
    $data['notification_id'] = $notification->id;
    $data['created_at'] = $notification->created_at->toDateTimeString();
    $data['is_read'] = $notification->read_at !== null;
    
    return $data;
}
    /**
     * Get unread notification count for the authenticated user for API consumption (Flutter app).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnreadCount(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $unreadCount = $user->unreadNotifications()->count();

            return response()->json(['success' => true, 'count' => $unreadCount], 200);

        } catch (\Exception $e) {
            Log::error('Failed to get unread notification count: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to retrieve count', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mark a specific notification as read from the Flutter app (API).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $notificationId  The ID of the notification to mark as read.
     * @return \Illuminate\Http\JsonResponse
     */
    public function markApiNotificationAsRead(Request $request, $notificationId)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $notification = $user->unreadNotifications()->where('id', $notificationId)->first();

            if ($notification) {
                $notification->markAsRead();
                return response()->json(['success' => true, 'message' => 'Notification marked as read']);
            }

            return response()->json(['success' => false, 'message' => 'Notification not found or already read'], 404);

        } catch (\Exception $e) {
            Log::error('Failed to mark API notification as read: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to mark notification as read', 'error' => $e->getMessage()], 500);
        }
    }
}