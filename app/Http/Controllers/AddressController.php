<?php

namespace App\Http\Controllers;

use App\Facades\MessageFixer;
use App\Facades\RegionFixer;
use App\Models\Address;
use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use App\Models\UserAddress;
use App\Models\Village;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    protected $address, $province, $regency, $district, $village, $userAddress;

    public function __construct(Address $address, Province $province, Regency $regency, District $district, Village $village, UserAddress $userAddress)
    {
        $this->address = $address;
        $this->province = $province;
        $this->regency = $regency;
        $this->district = $district;
        $this->village = $village;
        $this->userAddress = $userAddress;
    }

    public function index(Request $request)
    {
        $query = $this->userAddress->query();

        $query->where("user_id", $request->user()->id);

        if ($request->has('address_id')) {
            $query->where("id", $request->address_id);
        }

        if ($request->has('default')) {
            $query->where("is_default", 1);
        }

        $query->orderBy("is_default", "desc");

        $countAddress = $query->count();
        $addresses = $query->paginate($request->per_page);

        if ($request->has('address_id') || $request->has('default')) {
            return $this->detail($addresses->items());
        }

        return MessageFixer::render(
            code: $countAddress > 0 ? MessageFixer::DATA_OK : MessageFixer::DATA_NULL,
            message: $countAddress > 0 ? null : "Address no available.",
            data: $countAddress > 0 ? $addresses->items() : null,
            paginate: ($addresses instanceof LengthAwarePaginator) && $countAddress > 0  ? [
                "current_page" => $addresses->currentPage(),
                "last_page" => $addresses->lastPage(),
                "total" => $addresses->total(),
                "from" => $addresses->firstItem(),
                "to" => $addresses->lastItem(),
            ] : null
        );
    }

    public function default(Request $request)
    {
        $request->merge([
            'default' => 1
        ]);

        return $this->index($request);
    }

    protected function detail($address)
    {
        return MessageFixer::render(
            code: count($address) > 0 ? MessageFixer::DATA_OK : MessageFixer::DATA_NULL,
            message: count($address) > 0 ? null : "Address no available.",
            data: count($address) > 0 ? $address[0] : null
        );
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'province_id' => 'required|numeric|not_in:0',
            'regency_id' => 'required|numeric|not_in:0',
            'district_id' => 'required|numeric|not_in:0',
            'village_id' => 'required|numeric|not_in:0',
            'name' => 'required|max:100',
            'phone' => 'required|numeric|not_in:0',
            'detail' => 'max:200',
        ]);

        if ($validator->fails()) {
            return MessageFixer::render(code: MessageFixer::INVALID_BODY, message: 'Warning Process', data: $validator->errors());
        }

        $request->merge([
            'user_id' => $request->user()->id
        ]);

        try {
            if ($request->is_default == true) {
                $this->address->where('user_id', $request->user()->id)->update([
                    'is_default' => 0
                ]);
            }

            $request->merge([
                'is_default' => $request->is_default ? 1 : 0
            ]);

            $this->address->create($request->all());

            DB::commit();
            return MessageFixer::success(message: "Address has been saved");
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageFixer::error($th->getMessage());
        }
    }

    public function updateDefault(Request $request)
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'address_id' => 'required|exists:addresses,id'
        ]);

        if ($validator->fails()) {
            return MessageFixer::render(code: MessageFixer::INVALID_BODY, message: 'Warning Process', data: $validator->errors());
        }

        try {
            $this->address->where('user_id', $request->user()->id)->update([
                'is_default' => 0
            ]);

            $this->address->find($request->address_id)->update([
                'is_default' => 1
            ]);

            DB::commit();
            return MessageFixer::success(message: "Address has been updated");
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageFixer::error($th->getMessage());
        }
    }

    public function delete(Request $request)
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            'address_id' => 'required|exists:addresses,id'
        ]);

        if ($validator->fails()) {
            return MessageFixer::render(code: MessageFixer::INVALID_BODY, message: 'Warning Process', data: $validator->errors());
        }

        try {
            $this->address->find($request->address_id)->delete();

            DB::commit();
            return MessageFixer::success(message: "Address has been deleted");
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageFixer::error($th->getMessage());
        }
    }

    public function getProvinces(Request $request)
    {
        $query = $this->province->query();

        if ($request->has('search')) {
            $query->where("name", "like", "%$request->search%");
        }

        $countProvince = $query->count();
        $provinces = $query->paginate($request->per_page);

        return MessageFixer::render(
            code: $countProvince > 0 ? MessageFixer::DATA_OK : MessageFixer::DATA_NULL,
            message: $countProvince > 0 ? null : "Address no available.",
            data: $countProvince > 0 ? $provinces->items() : null,
            paginate: ($provinces instanceof LengthAwarePaginator) && $countProvince > 0  ? [
                "current_page" => $provinces->currentPage(),
                "last_page" => $provinces->lastPage(),
                "total" => $provinces->total(),
                "from" => $provinces->firstItem(),
                "to" => $provinces->lastItem(),
            ] : null
        );
    }

    public function getRegencies(Request $request)
    {
        $query = $this->regency->query();

        if ($request->has('province_id')) {
            $query->where('province_id', $request->province_id);
        }

        if ($request->has('search')) {
            $query->where("name", "like", "%$request->search%");
        }

        $countRegency = $query->count();
        $regencies = $query->paginate($request->per_page);

        return MessageFixer::render(
            code: $countRegency > 0 ? MessageFixer::DATA_OK : MessageFixer::DATA_NULL,
            message: $countRegency > 0 ? null : "Address no available.",
            data: $countRegency > 0 ? $regencies->items() : null,
            paginate: ($regencies instanceof LengthAwarePaginator) && $countRegency > 0  ? [
                "current_page" => $regencies->currentPage(),
                "last_page" => $regencies->lastPage(),
                "total" => $regencies->total(),
                "from" => $regencies->firstItem(),
                "to" => $regencies->lastItem(),
            ] : null
        );
    }

    public function getDistricts(Request $request)
    {
        $query = $this->district->query();

        if ($request->has('regency_id')) {
            $query->where('regency_id', $request->regency_id);
        }

        if ($request->has('search')) {
            $query->where("name", "like", "%$request->search%");
        }

        $countDistrict = $query->count();
        $districts = $query->paginate($request->per_page);

        return MessageFixer::render(
            code: $countDistrict > 0 ? MessageFixer::DATA_OK : MessageFixer::DATA_NULL,
            message: $countDistrict > 0 ? null : "Address no available.",
            data: $countDistrict > 0 ? $districts->items() : null,
            paginate: ($districts instanceof LengthAwarePaginator) && $countDistrict > 0  ? [
                "current_page" => $districts->currentPage(),
                "last_page" => $districts->lastPage(),
                "total" => $districts->total(),
                "from" => $districts->firstItem(),
                "to" => $districts->lastItem(),
            ] : null
        );
    }

    public function getVillages(Request $request)
    {
        $query = $this->village->query();

        if ($request->has('district_id')) {
            $query->where('district_id', $request->district_id);
        }

        if ($request->has('search')) {
            $query->where("name", "like", "%$request->search%");
        }

        $countVillage = $query->count();
        $villages = $query->paginate($request->per_page);

        return MessageFixer::render(
            code: $countVillage > 0 ? MessageFixer::DATA_OK : MessageFixer::DATA_NULL,
            message: $countVillage > 0 ? null : "Address no available.",
            data: $countVillage > 0 ? $villages->items() : null,
            paginate: ($villages instanceof LengthAwarePaginator) && $countVillage > 0  ? [
                "current_page" => $villages->currentPage(),
                "last_page" => $villages->lastPage(),
                "total" => $villages->total(),
                "from" => $villages->firstItem(),
                "to" => $villages->lastItem(),
            ] : null
        );
    }
}
