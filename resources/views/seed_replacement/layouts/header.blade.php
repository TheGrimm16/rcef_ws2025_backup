{{-- resources/views/layouts/header.blade.php --}}
<header id="app-header" style="position:fixed; top:0; left:0; right:0; height:80px; background:#2c3e50; color:white; display:flex; align-items:center; justify-content:space-between; padding:0 40px; z-index:1000;">
    
    {{-- App Title --}}
    <div style="flex:1; display:flex; justify-content:center; align-items:center;">
        <h1 style="font-size:2rem; margin:0; line-height:1; text-align:center;">Seed Replacement Portal</h1>
    </div>

    {{-- Navigation --}}
    <nav style="flex:1; display:flex; justify-content:flex-end; align-items:center; gap:25px;">
        <a href="{{ route('replacement.request.create') }}" 
           class="{{ Request::is('seed-replacement/request/create*') ? 'active' : '' }}" 
           style="color:white; text-decoration:none; padding:10px 12px; font-size:1rem;">
           Create Request
        </a>

        <!-- <a href="{{ route('replacement.dashboard') }}" 
           class="{{ Request::is('seed-replacement/dashboard') ? 'active' : '' }}" 
           style="color:white; text-decoration:none; padding:10px 12px; font-size:1rem;">
           Dashboard
        </a> -->

        <a href="{{ route('replacement.request.index') }}" 
           class="{{ Request::is('seed-replacement/request/view*') ? 'active' : '' }}" 
           style="color:white; text-decoration:none; padding:10px 12px; font-size:1rem;">
           Check Status
        </a>

        <a href="{{ route('replacement.users') }}" 
           class="{{ Request::is('seed-replacement/users*') ? 'active' : '' }}" 
           style="color:white; text-decoration:none; padding:10px 12px; font-size:1rem;">
           Users
        </a>

        <form id="logout-form" action="{{ route('replacement.logout') }}" method="POST" style="display:inline;">
            {{ csrf_field() }}
            <button type="submit" style="background:none; border:none; color:white; cursor:pointer; padding:10px 12px; font-size:1rem;">
                Logout
            </button>
        </form>
    </nav>
</header>

<style>
    /* Active link styling */
    #app-header nav a.active {
        background:#162330;
        font-weight:bold;
        border-radius:4px;
    }

    /* Hover effect */
    #app-header nav a:hover {
        background:#1e2f44;
        border-radius:4px;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('#app-header nav a');

    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });
});
</script>
