<?php

    require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

    $raw = file_get_contents("php://input");

    $data = json_decode($raw, true);

    $list_data = $data["list_data"] ?? [];

?>

<div class="card bg-white border-0 rounded-3 mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center p-4">
        <h2 class="mb-0">คอร์สเรียน</h2>

        <div class="d-flex gap-2">
            <a href="course_category.php" class="btn btn-info">
                จัดการหมวดหมู่
            </a>
            <a href="course_fromadd.php" class="btn btn-success">
                เพิ่มคอร์สเรียน
            </a>
        </div>
    </div>

    <div class="card-body p-4">

        <div class="default-table-area">

            <div class="table-responsive">

                <table class="table align-middle w-100" id="PageTable">

                    <thead>

                        <tr>

                            <th scope="col" class="text-center" style="width: 160px;">#</th>

                            <th scope="col" class="text-center" style="width: 160px;">รูป</th>

                            <th scope="col">ประเภท</th>

                            <th scope="col">ชื่อคอร์สเรียน</th>

                            <th scope="col">ราคา</th>
                            <th scope="col">รหัสวิชา</th>
                            <th scope="col">แสดงในหน้าหลัก</th>
                            <th scope="col">สถานะ</th>

                            <th scope="col" class="text-center" style="width: 120px;"></th>



                        </tr>

                    </thead>

                    <tbody>



                        <?php if (count($list_data) > 0): ?>

                            <?php foreach ($list_data as $row): ?>



                                <tr class="">

                                    <td class="text-center">
                                        <img src="<?php echo $row['course_cover_image']; ?>" class="w-25 h-25" alt="img">
                                    </td>

                                    <td class="text-center">

                                        <div>
                                            <span class="text-secondary"><i
                                                    class="ri-calendar-line me-1"></i><?php echo($row["course_type"] != "" ? $row["course_type"] : ""); ?></span>
                                        </div>
                                    </td>

                                    <td class="text-secondary"><?php echo $row["course_name"] ?? ""; ?></td>

                                    <td class="text-secondary"><?php echo $row["course_price"] ?? ""; ?></td>

                                    <td class="text-secondary">
                                        <div class="d-flex flex-column">
                                        <span class="text-secondary"><i
                                                class="ri-calendar-line me-1"></i><?php echo($row["course_code_cpd_1"] != "" ? $row["course_code_cpd_1"] : ""); ?></span>
                                        <span class="text-secondary"><i
                                                class="ri-calendar-line me-1"></i><?php echo($row["course_code_cpd_2"] != "" ? $row["course_code_cpd_2"] : ""); ?></span>
                                        <span class="text-secondary"><i
                                                class="ri-calendar-line me-1"></i><?php echo($row["course_code_cpd_3"] != "" ? $row["course_code_cpd_3"] : ""); ?></span>
                                        <span class="text-secondary"><i
                                                class="ri-calendar-line me-1"></i><?php echo($row["course_code_cpd_4"] != "" ? $row["course_code_cpd_4"] : ""); ?></span>
                                        <span class="text-secondary"><i
                                                class="ri-calendar-line me-1"></i><?php echo($row["course_code_cpa_1"] != "" ? $row["course_code_cpa_1"] : ""); ?></span>
                                        <span class="text-secondary"><i
                                                class="ri-calendar-line me-1"></i><?php echo($row["course_code_cpa_2"] != "" ? $row["course_code_cpa_2"] : ""); ?></span>
                                        <span class="text-secondary"><i
                                                class="ri-calendar-line me-1"></i><?php echo($row["course_code_cpa_3"] != "" ? $row["course_code_cpa_3"] : ""); ?></span>
                                        <span class="text-secondary"><i
                                                class="ri-calendar-line me-1"></i><?php echo($row["course_code_cpa_4"] != "" ? $row["course_code_cpa_4"] : ""); ?></span>
                                    </div>
                                    </td>

                                    <td>
                                        <span class="text-secondary"><i
                                                class="ri-calendar-line me-1"></i><?php echo($row["course_display"] != "" ? $row["course_display"] : ""); ?></span>
                                    </td>
                                    <td>
                                        <span class="text-secondary"><i
                                                class="ri-calendar-line me-1"></i><?php echo($row["course_status"] != "" ? $row["course_status"] : ""); ?></span>
                                    </td>



                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-warning w-100 mb-1"
                                            onclick="GetEditCourse('<?php echo $row['course_id']; ?>');">

                                            แก้ไข

                                        </button>

                                    </td>

                                </tr>



                            <?php endforeach; ?>

                        <?php endif; ?>



                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>



