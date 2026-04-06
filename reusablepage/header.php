<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm px-3">

    <!-- ☰ Sidebar Toggle (Mobile) -->
    <button class="btn btn-outline-success d-lg-none me-2" 
            data-bs-toggle="offcanvas" 
            data-bs-target="#sidebar">
        ☰
    </button>

    <!-- 🏥 Logo / System Name -->
    <a class="navbar-brand fw-bold text-success" href="#">
        💊 MMB'S DRUGSTORE
    </a>

    <!-- 🔍 Search (hidden on small screens) -->
    <form class="d-none d-md-flex ms-3">
        <input class="form-control border-success" type="search" placeholder="Search medicine...">
    </form>

    <!-- Right Side -->
    <div class="ms-auto d-flex align-items-center gap-2">

        <!-- 🔔 Notifications -->
        <button class="btn btn-outline-success position-relative">
            🔔
            <span class="position-absolute top-0 start-100 translate-middle badge bg-danger">
                3
            </span>
        </button>

        <!-- 👤 User Dropdown -->
        <div class="dropdown">
            <button class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                Admin
            </button>

            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li><a class="dropdown-item" href="#">Profile</a></li>
                <li><a class="dropdown-item" href="#">Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-danger" href="../login_logout_page/logout.php">
                        Logout
                    </a>
                </li>
            </ul>
        </div>

    </div>
</nav>