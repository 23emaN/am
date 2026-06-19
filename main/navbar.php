<?php
    // breadcrumb: ตั้งค่า $breadcrumbs ในแต่ละหน้า "ก่อน" include header.php
    // รูปแบบ: [ ['label' => 'คอร์สเรียน', 'url' => 'course'], ['label' => 'เพิ่มคอร์สเรียน'] ]
    $breadcrumbs = $breadcrumbs ?? [];
?>
<header class="header-area bg-white mb-4 rounded-bottom-15" id="header-area">
    <div class="d-flex align-items-center px-3 py-2">
        <nav aria-label="breadcrumb" class="w-100">
            <ol class="breadcrumb mb-0">
                <?php if (!empty($breadcrumbs)): ?>
                    <?php $last_key = array_key_last($breadcrumbs); ?>
                    <?php foreach ($breadcrumbs as $key => $bc): ?>
                        <?php $is_last = ($key === $last_key); $label = htmlspecialchars($bc['label'] ?? ''); ?>
                        <li class="breadcrumb-item<?php echo $is_last ? ' active' : ''; ?>">
                            <?php if (!$is_last && !empty($bc['url'])): ?>
                                <a href="<?php echo htmlspecialchars($bc['url']); ?>" class="text-decoration-none"><?php echo $label; ?></a>
                            <?php else: ?>
                                <strong><?php echo $label; ?></strong>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ol>
        </nav>
    </div>
</header>
