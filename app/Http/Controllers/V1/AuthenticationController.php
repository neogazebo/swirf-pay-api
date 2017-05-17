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

    const LOGIN_VIA_EMAIL = 1;
    const LOGIN_VIA_GOOGLE = 2;

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
                $account = $this->__signinCheckDB($request->input('email'), self::LOGIN_VIA_EMAIL, $request->input('password'));
                if($account == null)
                {
                    $arrInsert = [
                        'acc_email' => $request->input('email'),
                        'acc_signup_channel' => $request->input('signup_chanel'),
                        'acc_mobile_number' => $request->input('phone_number'),
                        'acc_password' => app('hash')->make($request->input('password')),
                        'acc_country' => $request->input('country'),
                        'acc_doku_id' => $doku,
                        'acc_app_uuid' => \Swirf::getAppId()
                    ];
                    $account = $this->__signup($arrInsert);
                }
                $this->data = [
                    'id' => $account->acc_id,
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

    private function __signinCheckDB($email, $via, $password = "", $social_media_id = "")
    {
        if($via == self::LOGIN_VIA_EMAIL)
        {
            $statement = 'select * from tbl_account where acc_email = :email and acc_app_uuid = :app_id  limit 0,1';
            $account = DB::select($statement, ['email' => $email, 'app_id' => \Swirf::getAppId()]);
            if(count($account) == 1)
            {
                if (app('hash')->check($password, $account[0]->acc_password)) {
                    return $account[0];
                }
                return false;
            }
        }
        return;
    }

    private function __signup($data)
    {
        $id = DB::table('tbl_account')->insertGetId($data);
        $account = DB::select("select * from tbl_account where acc_id = {$id} limit 0,1");
        return $account[0];
    }

    private function __getDoku()
    {
        return '1122323';
    }
}
