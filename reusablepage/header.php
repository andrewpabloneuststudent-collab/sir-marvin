<nav style="background:#1a2535; border-bottom:none; box-shadow:0 2px 16px rgba(0,0,0,.25); padding:0 20px; height:56px; display:flex; align-items:center; position:sticky; top:0; z-index:1040;">

    <!-- ☰ Mobile Toggle -->
    <button class="d-lg-none me-3" data-bs-toggle="offcanvas" data-bs-target="#sidebar"
        style="background:rgba(255,255,255,.1); border:none; border-radius:8px; color:#fff; width:36px; height:36px; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:.15s;">
        <i class="fas fa-bars" style="font-size:.9rem;"></i>
    </button>

    <!-- 💊 Brand -->
    <a href="#" style="text-decoration:none; display:flex; align-items:center; gap:10px;">
        <div style="width:32px; height:32px; background:linear-gradient(135deg,#c0392b,#e74c3c); border-radius:9px; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 8px rgba(192,57,43,.5);">
            <i class="fas fa-pills" style="color:#fff; font-size:.85rem;"></i>
        </div>
        <div style="line-height:1.1;">
            <span style="font-weight:800; color:#fff; font-size:.9rem; letter-spacing:-.2px;">MMB'S DRUGSTORE</span>
            <span style="display:block; font-size:.62rem; color:rgba(255,255,255,.45); letter-spacing:.5px; font-weight:500;">POINT OF SALE SYSTEM</span>
        </div>
    </a>

    <!-- Spacer -->
    <div style="flex:1;"></div>

    <!-- Right: User pill -->
    <div class="dropdown">
        <button class="dropdown-toggle" data-bs-toggle="dropdown"
            style="background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.15); border-radius:50px;
                   color:#fff; padding:6px 14px 6px 8px; display:flex; align-items:center; gap:8px;
                   font-size:.82rem; font-weight:600; cursor:pointer; transition:.15s; backdrop-filter:blur(8px);">
            <div style="width:28px; height:28px; background:linear-gradient(135deg,#c0392b,#e74c3c); border-radius:50%; display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-user" style="font-size:.7rem; color:#fff;"></i>
            </div>
            <?php echo htmlspecialchars($_SESSION['position'] ?? 'User'); ?>
        </button>

        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg" style="border-radius:14px; padding:8px; margin-top:10px; min-width:200px;">
            <li>
                <div style="padding:8px 12px 12px; margin-bottom:4px; border-bottom:1px solid #f1f5f9;">
                    <div style="font-size:.68rem; font-weight:600; text-transform:uppercase; letter-spacing:.6px; color:#94a3b8;">Logged in as</div>
                    <div style="font-weight:700; color:#1a2535; font-size:.88rem; margin-top:2px;">
                        <?php echo htmlspecialchars($_SESSION['username'] ?? $_SESSION['position'] ?? 'User'); ?>
                    </div>
                    <div style="font-size:.72rem; color:#c0392b; font-weight:600;"><?php echo htmlspecialchars($_SESSION['position'] ?? ''); ?></div>
                </div>
            </li>
            <li>
                <a class="dropdown-item d-flex align-items-center gap-2" href="#"
                    style="border-radius:8px; font-size:.83rem; padding:8px 12px; color:#374151;">
                    <i class="fas fa-user" style="color:#c0392b; width:16px; text-align:center;"></i> Profile
                </a>
            </li>
            <li>
                <a class="dropdown-item d-flex align-items-center gap-2" href="#"
                    style="border-radius:8px; font-size:.83rem; padding:8px 12px; color:#374151;">
                    <i class="fas fa-gear" style="color:#c0392b; width:16px; text-align:center;"></i> Settings
                </a>
            </li>
            <li><hr style="margin:4px 0; border-color:#f1f5f9;"></li>
            <li>
                <a class="dropdown-item d-flex align-items-center gap-2" href="../login_logout_page/logout.php"
                    style="border-radius:8px; font-size:.83rem; padding:8px 12px; color:#c0392b; font-weight:700;">
                    <i class="fas fa-right-from-bracket" style="width:16px; text-align:center;"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</nav>

<!-- ═══ GLOBAL NOTIFICATION MODAL ═══ -->
<div id="sysNotifModal"
    style="display:none; position:fixed; inset:0; background:rgba(15,15,30,0.65); z-index:999999; align-items:center; justify-content:center; backdrop-filter:blur(6px);"
    onclick="this.style.display='none'">
    <div onclick="event.stopPropagation()"
        style="background:#fff; border-radius:20px; max-width:380px; width:90%; overflow:hidden;
               box-shadow:0 30px 70px rgba(0,0,0,.3); animation:notifPop .2s cubic-bezier(.34,1.56,.64,1);">
        <div id="sysNotifBand" style="padding:30px 24px 22px; text-align:center;">
            <div id="sysNotifIconWrap" style="width:64px; height:64px; border-radius:20px; margin:0 auto 16px; display:flex; align-items:center; justify-content:center; font-size:1.8rem; font-weight:700;">
                <span id="sysNotifIcon"></span>
            </div>
            <h5 id="sysNotifTitle" style="font-weight:800; margin:0 0 8px; font-size:1.1rem; font-family:'Inter',sans-serif;"></h5>
            <p id="sysNotifMsg" style="color:#64748b; margin:0; font-size:.88rem; line-height:1.6; font-family:'Inter',sans-serif;"></p>
        </div>
        <div style="padding:0 24px 24px;">
            <button type="button" id="sysNotifOkBtn" onclick="document.getElementById('sysNotifModal').style.display='none'"
                style="width:100%; border:none; border-radius:12px; padding:12px; font-size:.9rem; font-weight:700;
                       cursor:pointer; transition:all .2s; font-family:'Inter',sans-serif; letter-spacing:.3px;">
                Got it
            </button>
        </div>
    </div>
</div>

<style>
@keyframes notifPop {
    from { opacity:0; transform:scale(.88) translateY(16px); }
    to   { opacity:1; transform:scale(1) translateY(0); }
}
/* Dropdown hover */
.dropdown-item:hover { background:#fff5f5 !important; }
</style>

<script>
function showNotif(msg, type) {
    var cfg = {
        error:   { icon:'✕', color:'#c0392b', bg:'#fff5f5', iconBg:'#fee2e2', btn:'linear-gradient(135deg,#c0392b,#e74c3c)', title:'Error'   },
        warning: { icon:'!', color:'#d97706', bg:'#fffbeb', iconBg:'#fef3c7', btn:'linear-gradient(135deg,#d97706,#f59e0b)', title:'Warning' },
        success: { icon:'✓', color:'#16a34a', bg:'#f0fdf4', iconBg:'#dcfce7', btn:'linear-gradient(135deg,#15803d,#22c55e)', title:'Success' },
        info:    { icon:'i', color:'#c0392b', bg:'#fff5f5', iconBg:'#fee2e2', btn:'linear-gradient(135deg,#c0392b,#e74c3c)', title:'Notice'  }
    };
    var t = cfg[type] || cfg.info;
    document.getElementById('sysNotifBand').style.background         = t.bg;
    document.getElementById('sysNotifIconWrap').style.background     = t.iconBg;
    document.getElementById('sysNotifIconWrap').style.color          = t.color;
    document.getElementById('sysNotifIcon').textContent              = t.icon;
    document.getElementById('sysNotifTitle').textContent             = t.title;
    document.getElementById('sysNotifTitle').style.color             = t.color;
    document.getElementById('sysNotifMsg').textContent               = msg;
    document.getElementById('sysNotifOkBtn').style.background        = t.btn;
    document.getElementById('sysNotifOkBtn').style.color             = '#fff';
    document.getElementById('sysNotifOkBtn').style.boxShadow         = '0 4px 14px rgba(0,0,0,.18)';
    document.getElementById('sysNotifModal').style.display           = 'flex';
}
function weposAlert(msg, type) { showNotif(msg, type || 'info'); }
</script>