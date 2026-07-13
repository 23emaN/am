<?php $now_page = str_replace('.php', '', basename($_SERVER['PHP_SELF'])); ?>
<?php $course_pages = ['course', 'course_fromadd', 'course_category']; ?>
<?php $remaining_pages = ['course_remaining']; ?>
<?php $order_pages = ['order', 'order_detail']; ?>
<?php $order_pending_pages = ['order_pending']; ?>
<?php $certificate_pages = ['course_certificate']; ?>
<?php $etax_pages = ['etax', 'etax_view', 'etax_edit']; ?>
<?php $etax_link_pages = ['etax_link', 'etax_link_fromadd', 'etax_link_view']; ?>
<?php $report_pages = ['report']; ?>
<?php $user_pages = ['user', 'user_edit']; ?>
<?php $history_pages = ['verify_history']; ?>
<?php $verify_pages = ['verify_request']; ?>
<?php $chat_pages = ['chat']; ?>
<?php $coupon_pages = ['coupon', 'coupon_fromadd', 'coupon_edit']; ?>
<?php $setting_pages = ['website_setting']; ?>
<?php $review_pages = ['reviews']; ?>
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
        $verify_pending = 0;
    }

    // จำนวนคำสั่งซื้อรอยืนยันการโอนเงิน (payment_status='0' AND payment_method='2') สำหรับ badge ข้างเมนู
    $pending_transfer_orders = 0;
    try {
        if (!empty($pdo_sidebar)) {
            $stmt_pending = $pdo_sidebar->query(
                "SELECT COUNT(*) FROM tbl_orders WHERE payment_status = '0' AND payment_method = '2'"
            );
            $pending_transfer_orders = (int) $stmt_pending->fetchColumn();
            $stmt_pending->closeCursor();
        }
    } catch (\Throwable $e) {
        $pending_transfer_orders = 0;
    }

    // จำนวนข้อความจากผู้เรียนที่ยังไม่อ่าน (sender_type='1' AND is_read='0') สำหรับ badge ข้างเมนู
    $chat_unread_msgs = 0;
    try {
        if (!empty($pdo_sidebar)) {
            $stmt_chat = $pdo_sidebar->query(
                "SELECT COUNT(*) FROM tbl_chat_messages
                 WHERE delete_at IS NULL AND sender_type = '1' AND is_read = '0'"
            );
            $chat_unread_msgs = (int) $stmt_chat->fetchColumn();
            $stmt_chat->closeCursor();
        }
    } catch (\Throwable $e) {
        $chat_unread_msgs = 0;
    }
?>

<div class="sidebar-area" id="sidebar-area">

    <div class="logo position-relative">
        <a href="home" class="d-block text-decoration-none position-relative">
            <img src="../assets/images/am-group-logo.png" alt="AM GROUP" style="height:40px; width:auto;">
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
           <li class="menu-item <?php echo in_array($now_page, $remaining_pages) ? 'open' : '' ?>">
                <a href="course_remaining" class="menu-link <?php echo in_array($now_page, $remaining_pages) ? 'active' : '' ?>">
                    <span class="material-symbols-outlined menu-icon">inventory</span>
                    <span class="title">คอร์สเรียนคงเหลือ</span>
                </a>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $order_pages) ? 'open' : '' ?>">
                <a href="order" class="menu-link <?php echo in_array($now_page, $order_pages) ? 'active' : '' ?>">
                    <span class="material-symbols-outlined menu-icon">receipt_long</span>
                    <span class="title">คำสั่งซื้อคอร์สเรียน</span>
                </a>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $chat_pages) ? 'open' : '' ?>">
                <a href="chat" class="menu-link <?php echo in_array($now_page, $chat_pages) ? 'active' : '' ?>">
                    <span class="material-symbols-outlined menu-icon">forum</span>
                    <span class="title">ตอบคำถามผู้เรียน</span>
                    <span class="badge rounded-pill bg-danger ms-auto" id="sidebarChatBadge" style="<?php echo $chat_unread_msgs > 0 ? '' : 'display:none;'; ?>"><?php echo $chat_unread_msgs > 99 ? '99+' : $chat_unread_msgs; ?></span>
                </a>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $order_pending_pages) ? 'open' : '' ?>">
                <a href="order_pending" class="menu-link <?php echo in_array($now_page, $order_pending_pages) ? 'active' : '' ?>">
                    <span class="material-symbols-outlined menu-icon">pending_actions</span>
                    <span class="title">คำสั่งซื้อรอยืนยัน</span>
                    <span class="badge rounded-pill bg-danger ms-auto" id="sidebarOrderBadge" style="<?php echo $pending_transfer_orders > 0 ? '' : 'display:none;'; ?>"><?php echo $pending_transfer_orders > 99 ? '99+' : $pending_transfer_orders; ?></span>
                </a>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $certificate_pages) ? 'open' : '' ?>">
                <a href="course_certificate" class="menu-link <?php echo in_array($now_page, $certificate_pages) ? 'active' : '' ?>">
                    <span class="material-symbols-outlined menu-icon">workspace_premium</span>
                    <span class="title">ใบรับรองผลการสอบ</span>
                </a>
            </li>

            <li class="menu-title small text-uppercase">
                <span class="menu-title-text">เอกสารทางบัญชี</span>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $etax_pages) ? 'open' : '' ?>">
                <a href="etax" class="menu-link <?php echo in_array($now_page, $etax_pages) ? 'active' : '' ?>">
                    <span class="material-symbols-outlined menu-icon">description</span>
                    <span class="title">ใบกำกับภาษี (E-Tax)</span>
                </a>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $etax_link_pages) ? 'open' : '' ?>">
                <a href="etax_link" class="menu-link <?php echo in_array($now_page, $etax_link_pages) ? 'active' : '' ?>">
                    <span class="material-symbols-outlined menu-icon">link</span>
                    <span class="title">ลิ้งค์ออกใบกำกับภาษี</span>
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
                    <span class="badge rounded-pill bg-danger ms-auto" id="sidebarVerifyBadge" style="<?php echo $verify_pending > 0 ? '' : 'display:none;'; ?>"><?php echo $verify_pending > 99 ? '99+' : $verify_pending; ?></span>
                </a>
            </li>

            <li class="menu-title small text-uppercase">
                <span class="menu-title-text">อื่น ๆ</span>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $report_pages) ? 'open' : '' ?>">
                <a href="report" class="menu-link <?php echo in_array($now_page, $report_pages) ? 'active' : '' ?>">
                    <span class="material-symbols-outlined menu-icon">summarize</span>
                    <span class="title">รายงาน/เอกสาร</span>
                </a>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $coupon_pages) ? 'open' : '' ?>">
                <a href="coupon" class="menu-link <?php echo in_array($now_page, $coupon_pages) ? 'active' : '' ?>">
                    <span class="material-symbols-outlined menu-icon">sell</span>
                    <span class="title">คูปองส่วนลด</span>
                </a>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $setting_pages) ? 'open' : '' ?>">
                <a href="website_setting" class="menu-link <?php echo in_array($now_page, $setting_pages) ? 'active' : '' ?>">
                    <span class="material-symbols-outlined menu-icon">settings</span>
                    <span class="title">ตั้งค่าเว็บไซต์</span>
                </a>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $review_pages) ? 'open' : '' ?>">
                <a href="reviews" class="menu-link <?php echo in_array($now_page, $review_pages) ? 'active' : '' ?>">
                    <span class="material-symbols-outlined menu-icon">reviews</span>
                    <span class="title">รีวิวจากลูกค้า</span>
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
