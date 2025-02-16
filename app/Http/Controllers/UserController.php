<?php

namespace App\Http\Controllers;

use App\Facades\MessageFixer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function show(Request $request)
    {
        $user = $request->user();

        $user->photo = asset(Storage::url($user->photo));

        return MessageFixer::render(MessageFixer::DATA_OK, "Get data successfully", $user);
    }

    public function update(Request $request)
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            "name" => "required|min:3|max:200",
            "email" => [
                "required",
                "min:3",
                "max:200",
                "email",
                Rule::unique("users", "email")->ignore(Auth::user()->id)
            ],
            "phone" => [
                "required",
                "min:8",
                "max:15",
                Rule::unique("users", "phone")->ignore(Auth::user()->id)
            ],
            "file" => [
                "image",
                "mimes:png,jpg,jpeg"
            ],
        ]);

        if ($validator->fails()) {
            return MessageFixer::render(code: MessageFixer::INVALID_BODY, message: 'Warning Process', data: $validator->errors());
        }

        $user = $this->user->find(Auth::user()->id);

        try {
            if ($request->hasFile('file')) {
                Storage::delete($user->photo);

                $request->merge([
                    "photo" => $request->file('file')->store('users')
                ]);
            }

            $user->update($request->except(['file']));

            DB::commit();
            return MessageFixer::success("Update user successfully!");
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageFixer::error($th->getMessage());
        }
    }

    public function changePassword(Request $request)
    {
        DB::beginTransaction();

        $validator = Validator::make($request->all(), [
            "current_password" => "required|min:8|max:200",
            "new_password" => "required|min:8|max:200|same:confirm_password",
            "confirm_password" => "required|min:8|max:200|same:new_password",
        ]);

        if ($validator->fails()) {
            return MessageFixer::render(code: MessageFixer::INVALID_BODY, message: 'Warning Process', data: $validator->errors());
        }

        $user = $this->user->find(Auth::user()->id);

        if (!Hash::check($request->current_password, $user->password)) {
            return MessageFixer::render(code: MessageFixer::WARNING_PROCESS, message: 'Current password is wrong!');
        }

        try {
            $user->update([
                "password" => Hash::make($request->new_password)
            ]);

            DB::commit();
            return MessageFixer::success("Update password successfully!");
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageFixer::error($th->getMessage());
        }
    }
}
