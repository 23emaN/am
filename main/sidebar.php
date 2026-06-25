<?php $now_page = str_replace('.php', '', basename($_SERVER['PHP_SELF'])); ?>
<?php $course_pages = ['course', 'course_fromadd', 'course_category']; ?>
<?php $user_pages = ['user', 'user_edit']; ?>
<?php $history_pages = ['verify_history']; ?>
<?php $verify_pages = ['verify_request']; ?>
<?php $coupon_pages = ['coupon', 'coupon_fromadd', 'coupon_edit']; ?>
<?php $banner_pages = ['banner', 'banner_fromadd', 'banner_edit']; ?>
<?php $admin_pages = ['admin', 'admin_fromadd', 'admin_edit']; ?>
<?php
    // จำนวนคำขอยืนยันตัวตนที่รอตรวจ (identity_verified = '1') สำหรับ badge ข้างเมนู
    $verify_pending = 0;
    try {
        require_once dirname(__DIR__) . '/vendor/autoload.php';
        $pdo_sidebar = (new \App\Database\Connection())->getPdo();
        if ($pdo_sidebar) {
            $stmt_sidebar = $pdo_sidebar->query(
                "SELECT COUNT(*) FROM tbl_user WHERE delete_at IS NULL AND identity_verified = '1'"
            );
            $verify_pending = (int) $stmt_sidebar->fetchColumn();
            $stmt_sidebar->closeCursor();
        }
    } catch (\Throwable $e) {
        $verify_pending = 0; // DB มีปัญหา -> ไม่ให้ sidebar พัง
    }
?>

<div class="sidebar-area" id="sidebar-area">

    <div class="logo position-relative">
        <a href="home" class="d-block text-decoration-none position-relative">
            <img src="../template/assets/images/logo-icon.png" alt="logo-icon">
            <span class="logo-text fw-bold text-dark">CPDTH</span>
        </a>
    </div>

    <aside id="layout-menu" class="layout-menu menu-vertical menu active" data-simplebar>
        <ul class="menu-inner">

            <li class="menu-title small text-uppercase">
                <span class="menu-title-text">MAIN</span>
            </li>

            <li class="menu-item <?php echo $now_page == 'home' ? 'open' : '' ?>">
                <a href="home" class="menu-link <?php echo $now_page == 'home' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined menu-icon">home</span>
                    <span class="title">หน้าแรก</span>
                </a>
            </li>

            <li class="menu-title small text-uppercase">
                <span class="menu-title-text">คอร์สเรียน</span>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $course_pages) ? 'open' : '' ?>">
                <a href="course" class="menu-link <?php echo in_array($now_page, $course_pages) ? 'active' : '' ?>">
                    <span class="material-symbols-outlined menu-icon">school</span>
                    <span class="title">คอร์สเรียน</span>
                </a>
            </li>

            <li class="menu-title small text-uppercase">
                <span class="menu-title-text">จัดการผู้ใช้</span>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $user_pages) ? 'open' : '' ?>">
                <a href="user" class="menu-link <?php echo in_array($now_page, $user_pages) ? 'active' : '' ?>">
                    <span class="material-symbols-outlined menu-icon">group</span>
                    <span class="title">ผู้ใช้/ลูกค้า</span>
                </a>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $history_pages) ? 'open' : '' ?>">
                <a href="verify_history" class="menu-link <?php echo in_array($now_page, $history_pages) ? 'active' : '' ?>">
                    <span class="material-symbols-outlined menu-icon">history</span>
                    <span class="title">ประวัติการยืนยันตัวตน</span>
                </a>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $verify_pages) ? 'open' : '' ?>">
                <a href="verify_request" class="menu-link <?php echo in_array($now_page, $verify_pages) ? 'active' : '' ?>">
                    <span class="material-symbols-outlined menu-icon">how_to_reg</span>
                    <span class="title">ยืนยันตัวตนผู้ใช้งาน</span>
                    <?php if ($verify_pending > 0): ?>
                        <span class="badge rounded-pill bg-danger ms-auto"><?php echo $verify_pending > 99 ? '99+' : $verify_pending; ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <li class="menu-title small text-uppercase">
                <span class="menu-title-text">อื่น ๆ</span>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $coupon_pages) ? 'open' : '' ?>">
                <a href="coupon" class="menu-link <?php echo in_array($now_page, $coupon_pages) ? 'active' : '' ?>">
                    <span class="material-symbols-outlined menu-icon">sell</span>
                    <span class="title">คูปองส่วนลด</span>
                </a>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $banner_pages) ? 'open' : '' ?>">
                <a href="banner" class="menu-link <?php echo in_array($now_page, $banner_pages) ? 'active' : '' ?>">
                    <span class="material-symbols-outlined menu-icon">image</span>
                    <span class="title">แบนเนอร์</span>
                </a>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $admin_pages) ? 'open' : '' ?>">
                <a href="admin" class="menu-link <?php echo in_array($now_page, $admin_pages) ? 'active' : '' ?>">
                    <span class="material-symbols-outlined menu-icon">manage_accounts</span>
                    <span class="title">ผู้ดูแลระบบ</span>
                </a>
            </li>

        </ul>
    </aside>

    <!-- โปรไฟล์ผู้ใช้ + ออกจากระบบ (ตรึงไว้ล่างสุดของ sidebar) -->
    <div class="sidebar-user d-flex align-items-center gap-2 p-3">
        <img class="rounded-circle ShowUserAvatar flex-shrink-0" src="../template/assets/images/administrator.jpg" alt="admin" style="width:42px;height:42px;object-fit:cover;">
        <div class="flex-grow-1 overflow-hidden">
            <div class="fw-semibold text-truncate ShowUserFullname"></div>
            <div class="small text-secondary text-truncate ShowUserRole"></div>
        </div>
        <a href="logout" class="logout-btn text-danger flex-shrink-0" title="ออกจากระบบ">
            <span class="material-symbols-outlined">logout</span>
        </a>
    </div>

</div>
