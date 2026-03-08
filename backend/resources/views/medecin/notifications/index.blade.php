@extends('layouts.app')

@section('content')
<div class="ml-64 p-6"> {{-- Assuming this provides necessary left margin for a sidebar --}}
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow rounded-lg overflow-hidden dark:bg-gray-800"> {{-- Added dark mode classes --}}
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Notifications</h2>
                @if(!$notifications->isEmpty()) {{-- Only show "Mark all as read" if there are notifications --}}
                    <button onclick="markAllAsRead()" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200">
                        Mark all as read
                    </button>
                @endif
            </div>

            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($notifications as $notification)
                <div class="px-6 py-4 hover:bg-gray-50 transition duration-150 ease-in-out dark:hover:bg-gray-700
                    {{ is_null($notification->read_at) ? 'bg-blue-50 dark:bg-blue-950' : 'dark:bg-gray-800' }}"> {{-- Dark mode bg for unread/read --}}
                    <div class="flex items-start">
                        <div class="flex-shrink-0 pt-1">
                            @if($notification->type === 'App\Notifications\NewConsultationRequest')
                            <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            @elseif($notification->type === 'App\Notifications\ConsultationStatusChanged') {{-- Explicitly check for status change notification --}}
                            <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            @else
                            {{-- Default icon for other notification types --}}
                            <svg class="h-6 w-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @endif
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $notification->data['message'] }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                            {{-- Link to consultation details --}}
                            @if(isset($notification->data['consultation_id']))
                            <a href="{{ route('doctor.showConsultation', $notification->data['consultation_id']) }}" class="mt-2 inline-flex items-center text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200">
                                View consultation
                                <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                            @endif
                        </div>
                        @if(is_null($notification->read_at))
                        {{-- Mark as Read Button (using a form for better robustness, or keep JS if preferred) --}}
                        <form action="{{ route('notifications.mark-as-read') }}" method="POST" class="ml-2">
                            @csrf
                            <input type="hidden" name="notification_id" value="{{ $notification->id }}">
                            <button type="submit" class="text-sm text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300" title="Mark as read">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
                        </form>
                        @else
                            <span class="ml-2 text-xs text-gray-400 dark:text-gray-500">Read</span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="px-6 py-8 text-center dark:bg-gray-800">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No notifications</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">You don't have any notifications yet.</p>
                </div>
                @endforelse
            </div>

            @if($notifications->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $notifications->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Using simple form submission for markAsRead as it's often more robust than AJAX for simple actions
    // This removes the need for JavaScript for each 'mark as read' button.
    // If you prefer AJAX, keep your existing JS functions and remove the <form> wrapper.
    function markAsRead(notificationId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("notifications.mark-as-read") }}';
        form.style.display = 'none';

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);

        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'POST'; // Or 'PUT' if your route uses PUT
        form.appendChild(methodInput);

        const notificationIdInput = document.createElement('input');
        notificationIdInput.type = 'hidden';
        notificationIdInput.name = 'notification_id';
        notificationIdInput.value = notificationId;
        form.appendChild(notificationIdInput);

        document.body.appendChild(form);
        form.submit();
    }

    function markAllAsRead() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("notifications.mark-all-read") }}';
        form.style.display = 'none';

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);

        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'POST'; // Or 'PUT' if your route uses PUT
        form.appendChild(methodInput);

        document.body.appendChild(form);
        form.submit();
    }
</script>
@endpush