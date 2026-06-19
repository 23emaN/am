<?php $breadcrumbs = [['label' => 'หน้าแรก']]; ?>
<?php include "header.php"; ?>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">

        <?php include "navbar.php"; ?>

        <div class="main-content-container overflow-hidden px-2">
            <h3 class="mb-0">หน้าแรก</h3>
        </div>

        <?php include "footer.php"; ?>
    </div>
</div>

<div class="modal fade" id="mainModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 1200px;">
        <div class="modal-content animated fadeIn" id="LoadingMainModal">
            <div id="showMainModal"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content animated fadeIn" id="LoadingMyModal">
            <div id="showModal"></div>
        </div>
    </div>
</div>

<div class="modal" id="subModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content animated fadeIn" id="LoadingSubModal">
            <div id="showSubModal"></div>
        </div>
    </div>
</div>

<?php include "script.php"; ?>

</body>

</html>
