<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("CREATE VIEW vw_user_addresses AS
            SELECT
                `a`.`id`          AS `id`,
                `a`.`user_id`     AS `user_id`,
                `a`.`province_id` AS `province_id`,
                `a`.`regency_id`  AS `regency_id`,
                `a`.`district_id` AS `district_id`,
                `a`.`village_id`  AS `village_id`,
                `a`.`name`        AS `name`,
                `a`.`phone`       AS `phone`,
                `a`.`detail`      AS `detail`,
                `p`.`name`        AS `province_name`,
                `r`.`name`        AS `regency_name`,
                `d`.`name`        AS `district_name`,
                `v`.`name`        AS `village_name`,
                `a`.`is_default`  AS `is_default`,
                `a`.`created_at`  AS `created_at`
            FROM `addresses` `a`
            LEFT JOIN `provinces` `p` ON `a`.`province_id` = `p`.`id`
            LEFT JOIN `regencies` `r` ON `a`.`regency_id` = `r`.`id`
            LEFT JOIN `districts` `d` ON `a`.`district_id` = `d`.`id`
            LEFT JOIN `villages` `v` ON `a`.`village_id` = `v`.`id`");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS vw_user_address");
    }
};
