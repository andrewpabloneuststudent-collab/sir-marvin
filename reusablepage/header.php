<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm px-3">

    <!-- ☰ Sidebar Toggle (Mobile) -->
    <button class="btn btn-outline-success d-lg-none me-2" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
        ☰
    </button>

    <!-- 🏥 Logo / System Name -->
    <a class="navbar-brand fw-bold text-success" href="#">
        MMB'S DRUGSTORE
    </a>

    <!-- Right Side -->
    <div class="ms-auto d-flex align-items-center gap-2">

        
            
            
        </button>

        <!-- 👤 User Dropdown -->
        <div class="dropdown">
            <button class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                <?php echo $_SESSION['position']; ?>
            </button>

            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li><a class="dropdown-item" href="#">Profile</a></li>
                <li><a class="dropdown-item" href="#">Settings</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <a class="dropdown-item text-danger" href="../login_logout_page/logout.php">
                        Logout
                    </a>
                </li>
            </ul>
        </div>

    </div>
</nav>