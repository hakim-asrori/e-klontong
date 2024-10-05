<?php

namespace App\Facades;

use Illuminate\Support\Facades\Http;

class RegionFixer
{
    const REGION_API = "https://emsifa.github.io/api-wilayah-indonesia/api/";

    public static function getProvinces($request)
    {
        $provinces = Http::get(self::REGION_API . "provinces.json");

        if (!$provinces->successful() || $provinces->status() != 200) {
            return MessageFixer::render(
                code: MessageFixer::DATA_NULL,
                message: "Province no available."
            );
        }

        return MessageFixer::render(
            code: count($provinces->json()) > 0 ? MessageFixer::DATA_OK : MessageFixer::DATA_NULL,
            message: count($provinces->json()) > 0 ? null : "Province no available.",
            data: count($provinces->json()) > 0 ? $provinces->json() : null
        );
    }

    public static function getProvince($request)
    {
        $province = Http::get(self::REGION_API . "province/$request[province_id].json");

        if (!$province->successful() || $province->status() != 200) {
            return null;
        }

        return $province->json();
    }

    public static function getRegencies($request)
    {
        $regencies = Http::get(self::REGION_API . "regencies/$request[province_id].json");

        if (!$regencies->successful() || $regencies->status() != 200) {
            return MessageFixer::render(
                code: MessageFixer::DATA_NULL,
                message: "Regency no available."
            );
        }

        return MessageFixer::render(
            code: count($regencies->json()) > 0 ? MessageFixer::DATA_OK : MessageFixer::DATA_NULL,
            message: count($regencies->json()) > 0 ? null : "Regency no available.",
            data: count($regencies->json()) > 0 ? $regencies->json() : null
        );
    }

    public static function getRegency($request)
    {
        $regency = Http::get(self::REGION_API . "regency/$request[regency_id].json");

        if (!$regency->successful() || $regency->status() != 200) {
            return null;
        }

        return $regency->json();
    }

    public static function getDistricts($request)
    {
        $districts = Http::get(self::REGION_API . "districts/$request[regency_id].json");

        if (!$districts->successful() || $districts->status() != 200) {
            return MessageFixer::render(
                code: MessageFixer::DATA_NULL,
                message: "District no available."
            );
        }

        return MessageFixer::render(
            code: count($districts->json()) > 0 ? MessageFixer::DATA_OK : MessageFixer::DATA_NULL,
            message: count($districts->json()) > 0 ? null : "District no available.",
            data: count($districts->json()) > 0 ? $districts->json() : null
        );
    }

    public static function getDistrict($request)
    {
        $district = Http::get(self::REGION_API . "district/$request[district_id].json");

        if (!$district->successful() || $district->status() != 200) {
            return null;
        }

        return $district->json();
    }

    public static function getVillages($request)
    {
        $villages = Http::get(self::REGION_API . "villages/$request[district_id].json");

        if (!$villages->successful() || $villages->status() != 200) {
            return MessageFixer::render(
                code: MessageFixer::DATA_NULL,
                message: "Village no available."
            );
        }

        return MessageFixer::render(
            code: count($villages->json()) > 0 ? MessageFixer::DATA_OK : MessageFixer::DATA_NULL,
            message: count($villages->json()) > 0 ? null : "Village no available.",
            data: count($villages->json()) > 0 ? $villages->json() : null
        );
    }

    public static function getVillage($request)
    {
        $village = Http::get(self::REGION_API . "village/$request[village_id].json");

        if (!$village->successful() || $village->status() != 200) {
            return null;
        }

        return $village->json();
    }
}
