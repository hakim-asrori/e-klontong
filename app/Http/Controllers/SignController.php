<?php

namespace App\Http\Controllers;

use App\Facades\MessageFixer;
use App\Http\Requests\Sign\InRequest;
use App\Http\Requests\Sign\UpRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SignController extends Controller
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function in(InRequest $request)
    {
        DB::beginTransaction();

        $user = $this->user->whereEmail($request->email)->first();
        if (!$user) {
            return MessageFixer::render(code: MessageFixer::DATA_NULL, message: 'Account not found');
        }

        if (!Hash::check($request->password, $user->password)) {
            return MessageFixer::render(code: MessageFixer::DATA_NULL, message: 'Account not found');
        }
        
        if (!$user->status) {
            return MessageFixer::render(code: MessageFixer::UNAUTHORIZATION, message: 'Account Suspend');
        }

        try {
            $token = $user->createToken('api', ['customer'])->plainTextToken;
            $user->token = $token;
            $user->photo = asset(Storage::url($user->photo));

            DB::commit();
            return MessageFixer::render(code: MessageFixer::DATA_OK, message: "Login Successfully", data: $user);
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageFixer::error($th->getMessage());
        }
    }

    public function up(UpRequest $request)
    {
        DB::beginTransaction();

        $request->merge([
            'password' => Hash::make($request->password)
        ]);

        try {
            $this->user->create($request->all());

            DB::commit();
            return MessageFixer::success("Account has been added");
        } catch (\Throwable $th) {
            DB::rollBack();
            return MessageFixer::error($th->getMessage());
        }
    }
}
