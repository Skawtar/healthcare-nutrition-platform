@section('navigation')
<nav class="fixed top-0 left-0 h-full w-64 rounded-lg shadow-lg bg-white flex flex-col">
    {{-- Logo Section --}}
    <div class="p-5 flex items-center space-x-3 border-b border-blue-100">
        <svg class="w-8 h-8 text-blue-400" fill="currentColor" viewBox="0 0 359.928 359.928" xmlns="http://www.w3.org/2000/svg" xml:space="preserve">
            <g>
                <path d="M115.604,293.4c5.95,5.224,12.091,10.614,18.41,16.202c0.043,0.039,0.087,0.077,0.131,0.115l21.018,18.155 c2.816,2.433,6.311,3.648,9.806,3.648s6.99-1.217,9.807-3.649l18.769-16.216c17.276,12.491,38.483,19.865,61.384,19.865 c57.897,0,105-47.103,105-105c0-29.95-12.607-57.009-32.789-76.155c1.842-8.121,2.789-16.328,2.789-24.509 c0-54.646-42.805-97.451-97.449-97.451c-24.561,0-48.827,9.249-67.512,25.279c-18.688-16.032-42.955-25.279-67.516-25.279 C42.806,28.406,0,71.212,0,125.857C0,191.912,45.99,232.286,115.604,293.4z M254.928,151.521c41.355,0,75,33.646,75,75 s-33.645,75-75,75c-41.355,0-75-33.646-75-75S213.572,151.521,254.928,151.521z"></path>
                <path d="M231.321,260.128c2.929,2.93,6.768,4.394,10.606,4.394c3.839,0,7.678-1.464,10.606-4.394l45-45 c5.858-5.858,5.858-15.355,0-21.213c-5.857-5.858-15.355-5.858-21.213,0l-34.393,34.393l-9.394-9.393 c-5.857-5.858-15.355-5.858-21.213,0c-5.858,5.857-5.858,15.355,0,21.213L231.321,260.128z"></path>
            </g>
        </svg>
        <span class="text-xl font-extrabold text-blue-400">Health Care</span>
    </div>

    {{-- Notifications Section --}}
    <div class="p-5 border-b border-blue-100" x-data="{ open: false }">
        <button @click="open = !open" class="w-full flex items-center p-2 text-blue-400 hover:bg-blue-50 rounded-lg group transition duration-200 ease-in-out focus:outline-none relative">
            <span class="sr-only">View notifications</span>
            <svg class="h-6 w-6 text-blue-400 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span class="ml-3 text-base">Notifications</span>
            @if(auth()->user()->unreadNotifications->count() > 0)
                <span class="absolute -top-1 right-2 block h-3 w-3 rounded-full bg-red-500 ring-2 ring-white"></span>
                <span class="ml-auto bg-blue-500 text-white text-xs font-bold px-2 py-0.5 rounded-full z-10">{{ auth()->user()->unreadNotifications->count() }}</span>
            @endif
        </button>

        <div x-show="open"
             @click.away="open = false"
             x-cloak
             class="origin-top-right absolute left-full ml-4 top-0 mt-0 w-80 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50 max-h-96 overflow-y-auto transform transition duration-100 ease-out"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="transform opacity-0 scale-95"
             x-transition:enter-end="transform opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="transform opacity-100 scale-100"
             x-transition:leave-end="transform opacity-0 scale-95">
            <div class="px-4 py-3 border-b border-blue-200 flex justify-between items-center bg-gray-50 rounded-t-md">
                <p class="text-sm font-semibold text-blue-900">Notifications</p>
                @if(auth()->user()->unreadNotifications->count() > 0)
                <button @click="markAllAsRead()" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                    Mark all as read
                </button>
                @endif
            </div>

            @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
            <a href="{{ $notification->data['type'] === 'new_consultation' ? route('medecin.consultations.show', $notification->data['consultation_id']) : '#' }}"
               class="block px-4 py-3 text-sm text-blue-700 hover:bg-blue-50 border-b border-blue-100 last:border-b-0 transition duration-150 ease-in-out"
               @click="markAsRead('{{ $notification->id }}')">
                <div class="flex items-start">
                    <div class="flex-shrink-0 pt-0.5">
                        @if($notification->type === 'App\Notifications\NewConsultationRequest')
                        <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd"></path>
                        </svg>
                        @else
                        <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        @endif
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-blue-900">
                            {{ $notification->data['message'] }}
                        </p>
                        <p class="mt-1 text-xs text-blue-500">
                            {{ $notification->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
            </a>
            @empty
            <div class="px-4 py-3 text-sm text-blue-500">
                No new notifications
            </div>
            @endforelse

            @if(auth()->user()->unreadNotifications->count() > 5)
            <div class="px-4 py-2 border-t border-blue-200 text-center">
                <a href="{{ route('notifications.index') }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                    View all notifications
                </a>
            </div>
            @endif
        </div>
    </div>

    {{-- Main Navigation Links --}}
    <div class="flex-1 overflow-y-auto py-5">
        <ul class="space-y-1 px-3">
         <li>
    <a href="{{ route('dashboard') }}" class="flex items-center p-3 text-blue-500 hover:bg-blue-50 hover:text-blue-700 rounded-lg group transition duration-200 ease-in-out {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700 font-semibold rounded-lg' : '' }}">
        <svg class="w-5 h-5 text-blue-500 group-hover:text-blue-700 transition duration-200 ease-in-out {{ request()->routeIs('dashboard') ? 'text-blue-700' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        <span class="ml-3 text-base">Tableau de bord</span>
    </a>
</li>
            <li>
                <a href="{{ route('patients.index')}}" class="flex items-center p-3 text-blue-500 hover:bg-blue-50 hover:text-blue-700 rounded-lg group transition duration-200 ease-in-out {{ request()->routeIs('patients.*') ? 'bg-blue-50 text-blue-700 font-semibold rounded-lg' : '' }}">
                    <svg class="w-5 h-5 text-blue-500 group-hover:text-blue-700 transition duration-200 ease-in-out {{ request()->routeIs('patients.*') ? 'text-blue-700' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="ml-3 text-base">Patients</span>
                </a>
            </li>
            <li>
                <a href="{{ route('consultations.index')}} " class="flex items-center p-3 text-blue-500 hover:bg-blue-50 hover:text-blue-700 rounded-lg group transition duration-200 ease-in-out {{ request()->routeIs('consultations.*') ? 'bg-blue-50 text-blue-700 font-semibold rounded-lg' : '' }}">
                    <svg class="w-5 h-5 text-blue-500 group-hover:text-blue-700 transition duration-200 ease-in-out {{ request()->routeIs('consultations.*') ? 'text-blue-700' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span class="ml-3 text-base">Consultations</span>
                </a>
            </li>
            <li>
                <a href="{{ route('documents.index') }}" class="flex items-center p-3 text-blue-500 hover:bg-bluz-50 hover:text-blue-700 rounded-lg group transition duration-200 ease-in-out {{ request()->routeIs('documents.*') ? 'bg-blue-50 text-blue-700 font-semibold rounded-lg' : '' }}">
                    <svg class="w-5 h-5 text-blue-500 group-hover:text-blue-700 transition duration-200 ease-in-out {{ request()->routeIs('documents.*') ? 'text-blue-700' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="ml-3 text-base">Documents Médicaux</span>
                </a>
            </li>
         <li>
             <a href="{{route('regimes.index')}}" class="flex items-center p-3 text-blue-500 hover:bg-blue-50 hover:text-blue-700 rounded-lg group transition duration-200 ease-in-out {{ request()->routeIs('regimes.*') ? 'bg-blue-50 text-blue-700 font-semibold rounded-lg' : '' }}">
        {{-- New SVG for "Régimes Alimentaires" --}}
        <svg class="w-5 h-5 text-blue-500 group-hover:text-blue-700 transition duration-200 ease-in-out {{ request()->routeIs('regimes.*') ? 'text-blue-700' : '' }}"
             fill="currentColor" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512.001 512.001" xml:space="preserve">
            <g>
                <g>
                    <rect x="106.017" y="159.533" width="179.701" height="33.391"></rect>
                </g>
            </g>
            <g>
                <g>
                    <rect x="106.017" y="79.772" width="179.701" height="33.391"></rect>
                </g>
            </g>
            <g>
                <g>
                    <path d="M486.118,253.418c-13.781-17.484-32.401-27.936-54.213-30.872c10.625-23.093,9.304-47.608,9.22-48.958l-0.909-14.727 l-14.726-0.908c-1.054-0.068-16.187-0.886-33.746,3.788V0.001H0v512h285.722v-1.154c4.727,0.759,9.53,1.154,14.397,1.154 c21.851,0,31.945-3.733,40.412-7.555c3.27-1.476,3.551-1.533,4.769-1.533c1.218,0,1.499,0.057,4.768,1.533 c8.466,3.823,18.56,7.555,40.412,7.555c40.622,0,76.936-26.832,99.631-73.619c14.115-29.096,21.888-63.464,21.889-96.774 C512.002,305.915,502.81,274.595,486.118,253.418z M379.176,205.117c7.175-7.174,17.907-10.802,27.3-12.518 c-1.715,9.403-5.343,20.13-12.515,27.303c-3.926,3.926-8.918,6.783-14.17,8.852c-0.206,0.08-0.408,0.161-0.613,0.242 c-4.169,1.588-8.485,2.689-12.548,3.43C368.226,223.376,371.699,212.596,379.176,205.117z M33.391,478.609V33.392h324.961v145.5 c-0.945,0.84-1.878,1.705-2.786,2.613c-3.619,3.619-6.671,7.559-9.274,11.656c-1.715-4.414-3.287-8.155-4.523-10.98l-30.593,13.38 c4.92,11.247,10.023,25.465,13.429,39.484c-8.019-4.154-17.363-8.259-27.582-10.5c-30.734-6.741-57.935-1.488-78.495,14.76 H106.022v33.391h86.634c-6.423,13.46-10.746,29.175-12.751,46.377h-73.883v33.391h72.867c0.777,15.594,3.203,31.262,7.143,46.376 h-80.01v33.391h91.621c0.919,2.073,1.863,4.127,2.845,6.15c7.648,15.766,16.848,29.256,27.233,40.228H33.391z M460.069,423.809 c-9.928,20.466-32.171,54.801-69.589,54.801c-16.491,0-21.9-2.442-26.671-4.597c-4.432-2.001-9.948-4.491-18.51-4.491 c-8.563,0-14.078,2.49-18.51,4.491c-4.773,2.155-10.18,4.597-26.672,4.597c-37.417,0-59.659-34.335-69.587-54.801 c-24.753-51.029-24.675-118.192,0.175-149.72c13.334-16.916,33.242-22.611,59.165-16.926c10.592,2.322,20.701,8.093,29.62,13.184 c9.7,5.537,17.361,9.909,25.809,9.909c8.447,0,16.109-4.373,25.808-9.909c8.921-5.091,19.031-10.862,29.62-13.184 c25.927-5.687,45.832,0.009,59.165,16.926C484.744,305.616,484.822,372.78,460.069,423.809z"></path>
                </g>
            </g>
        </svg>
        <span class="ml-3 text-base">Régimes Alimentaires</span>
    </a>
</li>
            
            <li>
<a href="{{route('doctor.medicalRecordsHistory')}}" class="flex items-center p-3 text-blue-500 hover:bg-blue-50 hover:text-blue-700 rounded-lg group transition duration-200 ease-in-out {{ request()->routeIs('dossiers.*') ? 'bg-blue-50 text-blue-700 font-semibold rounded-lg' : '' }}">
       <svg class="w-5 h-5 text-blue-500 group-hover:text-blue-700 transition duration-200 ease-in-out{{ request()->routeIs('dossiers.*') ? 'text-blue-700' : '' }}"
             viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="currentColor">
            <g>
                <path fill="none" d="M0 0H24V24H0z"></path>
                <path d="M17 2v2h3c.552 0 1 .448 1 1v16c0 .552-.448 1-1 1H4c-.552 0-1-.448-1-1V5c0-.552.448-1 1-1h3V2h10zM7 6H5v14h14V6h-2v2H7V6zm6 5v2h2v2h-2.001L13 17h-2l-.001-2H9v-2h2v-2h2zm2-7H9v2h6V4z"></path>
            </g>
        </svg>
        <span class="ml-3 text-base"> Médical Records</span>
    </a>
</li>
            <li>
                <a href="{{route('doctor.medicalHistory')}}" class="flex items-center p-3 text-blue-500 hover:bg-blue-50 hover:text-blue-700 rounded-lg group transition duration-200 ease-in-out {{ request()->routeIs('medical_records.*') ? 'bg-blue-50 text-blue-700 font-semibold rounded-lg' : '' }}">
                    <svg class="w-5 h-5 text-blue-500 group-hover:text-blue-700 transition duration-200 ease-in-out {{ request()->routeIs('medical_records.*') ? 'text-blue-700' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 0v4m-3 0V7m-4 0h6m-6 4h6m-6 4h6m-6 4h6m-6 4h6m-6 4h6" />
                    </svg>
                    <span class="ml-3 text-base">Historique Médical</span>
                </a>
            </li>
        </ul>
    </div>

    {{-- User Profile and Logout at the Bottom --}}
    <div class="p-5 border-t border-blue-100 relative" x-data="{ open: false }">
        <button @click="open = !open" class="w-full flex items-center space-x-3 hover:bg-blue-50 transition duration-200 ease-in-out cursor-pointer p-2 rounded-lg">
            <div class="relative">
                <img src= " {{ asset('storage/profiles/'.auth()->user()->image_profil)  ?? 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=random' }}"
                     class="w-10 h-10 rounded-full border-2 border-blue-400 object-cover shadow-sm">
            </div>
            <div class="flex-1 text-left">
                <p class="font-semibold text-blue-800 text-sm truncate"> {{ auth()->user()->name }}</p>
                <p class="text-xs text-blue-600 truncate">{{ auth()->user()->specialite_code }}</p>
            </div>
            <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4z"></path>
            </svg>
        </button>

        <div x-show="open"
             @click.away="open = false"
             x-cloak
             class="absolute left-full ml-2 bottom-0 w-48 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5 focus:outline-none z-50 transform transition duration-100 ease-out"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="transform opacity-0 scale-95"
             x-transition:enter-end="transform opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="transform opacity-100 scale-100"
             x-transition:leave-end="transform opacity-0 scale-95">
            <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-blue-700 hover:bg-blue-50">Profile</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-blue-700 hover:bg-blue-50">Déconnexion</button>
            </form>
        </div>
    </div>
</nav>

@push('scripts')
<script>
  
    function markAsRead(notificationId) {
        fetch('{{ route("notifications.mark-as-read") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ notification_id: notificationId })
        }).then(response => {
            if (response.ok) {
                window.location.reload();
            }
        });
    }

    function markAllAsRead() {
        fetch('{{ route("notifications.mark-all-read") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(response => {
            if (response.ok) {
                window.location.reload();
            }
        });
    }
</script>
@endpush