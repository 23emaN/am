<?php $now_page = str_replace('.php', '', basename($_SERVER['PHP_SELF'])); ?>

<div class="sidebar-area" id="sidebar-area">

    <div class="logo position-relative">

        <a href="home" class="d-block text-decoration-none position-relative">

            <img src="../template/assets/images/logo-icon.png" alt="logo-icon">

            <span class="logo-text fw-bold text-dark">CPDTH</span>

        </a>

        <button
            class="sidebar-burger-menu bg-transparent p-0 border-0 opacity-0 z-n1 position-absolute top-50 end-0 translate-middle-y"
            id="sidebar-burger-menu">

            <i data-feather="x"></i>

        </button>

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

            <li class="menu-item <?php echo $now_page == 'course' ? 'open' : '' ?>">

                <a href="course" class="menu-link <?php echo $now_page == 'course' ? 'active' : '' ?>">

                    <span class="material-symbols-outlined menu-icon">school</span>

                    <span class="title">คอร์สเรียน</span>

                </a>

            </li>

            <li class="menu-title small text-uppercase">

                <span class="menu-title-text">OTHER</span>

            </li>

            <li class="menu-item">

                <a href="logout" class="menu-link">

                    <span class="material-symbols-outlined menu-icon">logout</span>

                    <span class="title">ออกจากระบบ</span>

                </a>

            </li>

        </ul>

    </aside>

</div>