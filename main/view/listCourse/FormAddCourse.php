<?php

    require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

    $raw = file_get_contents("php://input");

    $data = json_decode($raw, true);

    $list_data = $data["list_data"] ?? [];

?>
<style>
    /* บังคับให้ช่องกรอกเป็นสีขาวและเอาสีเทาออก */
.form-control {
    background-color: #ffffff !important;
}

/* กรณีต้องการให้เวลา Focus แล้วยังเป็นสีขาวอยู่ */
.form-control:focus {
    background-color: #ffffff !important;
}

/* ปรับความสูงของ Select2 ให้มีขนาด 38px เท่ากับ input ช่องอื่น */
.select2-container .select2-selection--single {
    height: 38px !important;
    border: 1px solid #ced4da !important;
    border-radius: 0.375rem !important;
    background-color: #ffffff !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px !important;
    padding-left: 0.75rem !important;
    color: var(--bs-body-color) !important;
}
.select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: #6c757d !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
}
</style>

<div class="card bg-white border-0 rounded-3 mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center p-4 border-0">
        <h2 class="mb-0">เพิ่มคอร์สเรียนใหม่</h2>
    </div>
    <div class="px-4 mb-3"> <div class="alert alert-success mb-0" role="alert">
        คุณสามารถเพิ่มข้อมูลบทเรียนและข้อสอบได้หลังจากเพิ่มคอร์สเรียน
    </div>
</div>
    <div class="card-body p-4">
        <form id="formAddCourse" enctype="multipart/form-data">
            <!-- ข้อมูลทั่วไป -->
            <h5 class="mb-3 border-bottom pb-2">ข้อมูลทั่วไป</h5>
            <div class="row g-3 mb-4">

                <div class="col-md-4">
                    <label for="course_name" class="form-label fw-medium">รูปหน้าปก<span class="text-danger">*</span></label>
                     <input type="file" class="form-control" id="course_cover_image" name="course_cover_image" accept="image/*">
                </div>

                <div class="col-md-8">
                    <label for="course_price" class="form-label fw-medium">ชื่อคอร์สเรียน<span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="course_name" name="course_name" required placeholder="กรอกชื่อคอร์สเรียน">
                </div>

                <div class="col-md-4">
                    <label for="course_type" class="form-label fw-medium">ประเภท<span class="text-danger">*</span></label>
                     <input type="text" class="form-control" id="course_type" name="course_type" required placeholder="ประเภทคอร์สเรียน">
                </div>

                  <div class="col-md-4">
                    <label for="course_category" class="form-label fw-medium">หมวดหมู่<span class="text-danger">*</span></label>
                    <select class="form-select" id="course_category" name="course_category" required>
                        <option value="" disabled selected>เลือกหมวดหมู่</option>
                        <?php foreach ($list_data as $row): ?>
                            <option value="<?php echo htmlspecialchars($row['group_id'] ?? ''); ?>">
                                <?php echo htmlspecialchars($row['group_name'] ?? ''); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="course_instructor" class="form-label fw-medium">ผู้บรรยาย/ผู้สอน<span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="course_instructor" name="course_instructor" required placeholder="กรอกผู้บรรยาย/ผู้สอน">
                </div>
            </div>

            <!-- รหัสวิชา CPD -->
            <h5 class="mb-3 border-bottom pb-2">รหัสวิชา CPD</h5>
            <div class="row g-3 mb-4">

                <div class="col-md-3">
                    <label for="course_code_cpd_1" class="form-label fw-medium">รหัสวิชา CPD 1</label>
                    <input type="text" class="form-control" id="course_code_cpd_1" name="course_code_cpd_1" placeholder="กรอกรหัส CPD 1">
                </div>

                <div class="col-md-3">
                    <label for="course_code_cpd_2" class="form-label fw-medium">รหัสวิชา CPD 2</label>
                    <input type="text" class="form-control" id="course_code_cpd_2" name="course_code_cpd_2" placeholder="กรอกรหัส CPD 2">
                </div>

                <div class="col-md-3">
                    <label for="course_code_cpd_3" class="form-label fw-medium">รหัสวิชา CPD 3</label>
                    <input type="text" class="form-control" id="course_code_cpd_3" name="course_code_cpd_3" placeholder="กรอกรหัส CPD 3">
                </div>

                <div class="col-md-3">
                    <label for="course_code_cpd_4" class="form-label fw-medium">รหัสวิชา CPD 4</label>
                    <input type="text" class="form-control" id="course_code_cpd_4" name="course_code_cpd_4" placeholder="กรอกรหัส CPD 4">
                </div>

            </div>

            <!-- รหัสวิชา CPA -->
            <h5 class="mb-3 border-bottom pb-2">รหัสวิชา CPA</h5>
            <div class="row g-3 mb-4">

                <div class="col-md-3">
                    <label for="course_code_cpa_1" class="form-label fw-medium">รหัสวิชา CPA 1</label>
                    <input type="text" class="form-control" id="course_code_cpa_1" name="course_code_cpa_1" placeholder="กรอกรหัส CPA 1">
                </div>

                <div class="col-md-3">
                    <label for="course_code_cpa_2" class="form-label fw-medium">รหัสวิชา CPA 2</label>
                    <input type="text" class="form-control" id="course_code_cpa_2" name="course_code_cpa_2" placeholder="กรอกรหัส CPA 2">
                </div>

                <div class="col-md-3">
                    <label for="course_code_cpa_3" class="form-label fw-medium">รหัสวิชา CPA 3</label>
                    <input type="text" class="form-control" id="course_code_cpa_3" name="course_code_cpa_3" placeholder="กรอกรหัส CPA 3">
                </div>

                <div class="col-md-3">
                    <label for="course_code_cpa_4" class="form-label fw-medium">รหัสวิชา CPA 4</label>
                    <input type="text" class="form-control" id="course_code_cpa_4" name="course_code_cpa_4" placeholder="กรอกรหัส CPA 4">
                </div>

            </div>

            <!-- การแสดงผลและสถานะ -->
            <h5 class="mb-3 border-bottom pb-2">การตั้งค่าการแสดงผล</h5>
            <div class="row g-3 mb-4">

                <div class="col-md-6">
                    <label for="course_display" class="form-label fw-medium">แสดงในหน้าหลัก</label>
                    <select class="form-select" id="course_display" name="course_display">
                        <option value="1" selected>แสดง</option>
                        <option value="0">ไม่แสดง</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="course_status" class="form-label fw-medium">สถานะคอร์สเรียน</label>
                    <select class="form-select" id="course_status" name="course_status">
                        <option value="1" selected>เปิดใช้งาน</option>
                        <option value="0">ปิดใช้งาน</option>
                    </select>
                </div>

            </div>

            <!-- ปุ่มกดยืนยัน -->
            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                <button type="button" class="btn btn-secondary px-4" onclick="LoadData();">
                    <i class="ri-close-line me-1"></i> ยกเลิก
                </button>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="ri-save-line me-1"></i> บันทึกข้อมูล
                </button>
            </div>
        </form>
    </div>
</div>


