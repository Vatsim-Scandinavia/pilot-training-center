<nav>

    <ul class="navbar-nav sidebar" id="sidebar">

        {{-- Sidebar - Brand --}}
        <a class="sidebar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
            <div class="sidebar-brand-icon">
                <img src="{{ asset('images/pilot.svg') }}">
            </div>

            <div class="sidebar-brand-text mx-3">{{ config('app.name') }}</div>

            <button type="button" id="sidebar-button-close" class="sidebar-button-close ms-auto">
                <i class="fas fa-times"></i>
            </button>
        </a>

        {{-- Divider --}}
        <div class="sidebar-divider my-0"></div>

        <li class="nav-item {{ Route::is('dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('dashboard') }}">
            <i class="fas fa-fw fa-table-columns"></i>
            <span>Dashboard</span></a>
        </li>

        @can('update', [\App\Models\Task::class])
            <li class="nav-item {{ Route::is('tasks') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('tasks') }}">
                    <i class="fas fa-fw fa-list"></i>
                    <span>Tasks</span>
                    @if(\Auth::user()->tasks->where('status', \App\Helpers\TaskStatus::PENDING)->count())
                        <span class="badge text-bg-danger">{{ \Auth::user()->tasks->where('status', \App\Helpers\TaskStatus::PENDING)->count() }}</span>
                    @endif
                </a>
            </li>
        @endcan

        @if(Setting::get('linkMoodle') && Setting::get('linkMoodle') != "")
            <li class="nav-item">
            <a class="nav-link" href="{{ Setting::get('linkMoodle') }}" target="_blank">
                <i class="fas fa-graduation-cap"></i>
                <span>Moodle</span></a>
            </li>
        @endif

        @if (Setting::get('linkWiki') && Setting::get('linkWiki') != "")
            <li class="nav-item">
                <a class="nav-link" href="{{ Setting::get('linkWiki') }}" target="_blank">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Wiki</span></a>
            </li>
        @endif

        

        {{-- Divider --}}
        <div class="sidebar-divider"></div>

        {{-- Heading --}}
        <div class="sidebar-heading">
        Pilot Training
        </div>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('pilot.training.apply') }}">
                <i class="fas fa-graduation-cap"></i>
                <span>Request Pilot Training</span>
            </a>
        </li>

        @if (\Auth::user()->isInstructor())
            {{-- Nav Item - Pages Collapse Menu --}}
            <li class="nav-item {{ Route::is('pilot.requests') || Route::is('pilot.requests.history') ? 'active' : '' }}">
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapsePilotReq" aria-expanded="true" aria-controls="collapsePilotReq">
                <i class="fas fa-fw fa-flag"></i>
                <span>Training Requests</span>
            </a>
            <div id="collapsePilotReq" class="collapse" data-bs-parent="#sidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                
                <a class="collapse-item" href="{{ route('pilot.requests') }}">Open Requests</a>
                
                <a class="collapse-item" href="{{ route('pilot.requests.history') }}">Closed Requests</a> 
                
                </div>
            </div>
            </li>
        @endif

        {{-- Divider --}}
        <div class="sidebar-divider"></div>

        {{-- Heading --}}
        <div class="sidebar-heading">
        Members
        </div>

        @if (\Auth::user()->isInstructorOrAbove())

            {{-- Nav Item - Pages Collapse Menu --}}
            <li class="nav-item {{ Route::is('users') || Route::is('users.other') ? 'active' : '' }}">
                <a class="nav-link collapsed" href="{{route('users')}}">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Users</span>
                </a>
            </li>
            
        @endif

        {{-- Nav Item - Pages Collapse Menu --}}
        <li class="nav-item {{ Route::is('roster') ? 'active' : '' }}">

            <a class="nav-link" href="{{ route('roster') }}">
                <i class="fas fa-fw fa-address-book"></i>
                <span>Instructor Roster</span>
            </a>

        </li>
        

        @if (\Auth::user()->isAdmin())
            {{-- Divider --}}
            <div class="sidebar-divider"></div>

            {{-- Nav Item - Pages Collapse Menu --}}
            <li class="nav-item {{ Route::is('reports.trainings') || Route::is('reports.mentors') || Route::is('reports.access') ? 'active' : '' }}">
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
                <i class="fas fa-fw fa-clipboard-list"></i>
                <span>Reports</span>
            </a>
            <div id="collapseTwo" class="collapse" data-bs-parent="#sidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                
                @if(\Auth::user()->isAdmin())
                    <a class="collapse-item" href="{{ route('reports.trainings') }}">Trainings</a>
                @elseif(\Auth::user()->isModerator())
                    <a class="collapse-item" href="{{ route('reports.trainings') }}">Trainings</a>
                @endif
                
                @if(\Auth::user()->isAdmin())
                    <a class="collapse-item" href="{{ route('reports.activities')}}">Activities</a>
                @elseif(\Auth::user()->isModerator())
                    <a class="collapse-item" href="{{ route('reports.activities')}}">Activities</a>
                @endif

                <a class="collapse-item" href="{{ route('reports.instructors') }}">Instructors</a>

                @can('viewAccessReport', \App\Models\ManagementReport::class)
                    <a class="collapse-item" href="{{ route('reports.access') }}">Access</a>
                @endcan
                
                </div>
            </div>
            </li>
        @endif

        @if (\Auth::user()->isAdmin())

            {{-- Nav Item - Utilities Collapse Menu --}}
            <li class="nav-item {{ Route::is('admin.settings') || Route::is('admin.logs') ? 'active' : '' }}">
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseUtilities" aria-expanded="true" aria-controls="collapseUtilities">
                <i class="fas fa-fw fa-cogs"></i>
                <span>Administration</span>
            </a>
            <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities" data-bs-parent="#sidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                @if (\Auth::user()->isAdmin())
                    <a class="collapse-item" href="{{ route('admin.settings') }}">Settings</a>
                    <a class="collapse-item" href="{{ route('admin.logs') }}">Logs</a>
                @endif
                </div>
            </div>
            </li>

        @endif

        {{-- Divider --}}
        <div class="sidebar-divider d-none d-md-block"></div>

        @if(Config::get('app.env') != "production")
            <div class="alert alert-warning mt-2 fs-sm" role="alert">
                Development Env
            </div>
        @endif

        {{--  Logo and version element --}}
        <div class="d-flex flex-column align-items-center mt-auto mb-3">
            <a href="{{ Setting::get('linkHome') }}" class="d-block"><img class="logo" src="{{ asset('images/logos/'.Config::get('app.logo')) }}"></a>
            <a href="https://github.com/Vatsim-Scandinavia/pilot-training-center/releases" target="_blank" class="version">Pilot Training Center v{{ config('app.version') }}</a>
        </div>
        
    </ul>

</nav>