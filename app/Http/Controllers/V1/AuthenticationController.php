<?php

namespace App\Http\Controllers\v1;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper as RH;
use Validator;
use DB;

class AuthenticationController extends BaseController
{
    use AppTrait;

    public function signup(Request $request){

        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'signup_chanel' => 'required',
            'phone_number' => 'required',
            'password' => 'required',
            'country' => 'required',
        ]);

        if (!($validator->fails()))
        {
            //todo integration with doku
            $doku = $this->__getDoku();

            if($doku !== false)
            {
                $id = DB::table('tbl_account')->insertGetId(
                    [
                        'acc_email' => $request->input('email'),
                        'acc_signup_channel' => $request->input('signup_chanel'),
                        'acc_mobile_number' => $request->input('phone_number'),
                        'acc_password' => $request->input('password'),
                        'acc_country' => $request->input('country'),
                        'acc_doku_id' => $doku
                    ]
                );
                $this->data = [
                    'id' => $id,
                    'email' => $request->input('email'),
                    'signup_channel' => $request->input('signup_chanel'),
                    'mobile_number' => $request->input('phone_number'),
                    'password' => $request->input('password'),
                    'country' => $request->input('country')
                ];
                $this->success = true;
            }
        }
        else
        {
            $this->code = RH::HTTP_BAD_REQUEST;
            $this->message = $validator->errors()->all()[0];
        }

        return $this->json();
    }

    private function __getDoku()
    {
        return '1122323';
    }
}
