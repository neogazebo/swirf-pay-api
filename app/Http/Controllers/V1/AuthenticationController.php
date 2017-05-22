<?php

namespace App\Http\Controllers\V1;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper as RH;
use App\Helpers\TokenHelper as TH;
use Validator;
use DB;
use App\Helpers\DokuHelper as DH;

class AuthenticationController extends BaseController
{
    use AppTrait;

    const LOGIN_VIA_EMAIL = 1;
    const LOGIN_VIA_GOOGLE = 2;
    const ACCOUNT_ACTIVE = 1;
    const ACCOUNT_INACTIVE = 0;

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
            $account = $this->__signinCheckDB($request->input('email'), self::LOGIN_VIA_EMAIL, $request->input('password'));
            if($account === null)
            {
                $account = $this->__signup($request);
                if($account !== false)
                {
                    $this->success = true;
                }
            }
            else
            {
                if($account === false)
                {
                    $this->code = RH::HTTP_UNAUTHORIZED;
                    $this->message = "Wrong email/password";
                }
                else
                {
                    $this->success = true;
                }
            }

            if($this->success === true)
            {
                $this->__afterLogin($account);
            }
        }
        else
        {
            $this->code = RH::HTTP_BAD_REQUEST;
            $this->message = $validator->errors()->all()[0];
        }

        return $this->json();
    }

    private function __signinCheckDB($email, $via = self::LOGIN_VIA_EMAIL, $password = "", $social_media_id = "")
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
        return null;
        // todo create condition for via Google
    }

    /**
     * @param $data
     * @param $doku
     * @return bool
     */
    private function __signup($request)
    {
        $arrInsert = [
            'acc_email' => $request->input('email'),
            'acc_signup_channel' => $request->input('signup_chanel'),
            'acc_mobile_number' => $request->input('phone_number'),
            'acc_password' => app('hash')->make($request->input('password')),
            'acc_country' => $request->input('country'),
            'acc_app_uuid' => \Swirf::getAppId(),
            'acc_created_at' => time()
        ];

        $id = DB::table('tbl_account')->insertGetId($arrInsert);
        if(!empty($id))
        {
            $account = DB::select("select * from tbl_account where acc_id = {$id} limit 0,1");
            return $account[0];
        }
        return false;
    }

    private function __afterLogin($account)
    {
        if($account->acc_status == self::ACCOUNT_ACTIVE)
        {
            $token = json_encode(['account_id' => $account->acc_id, 'doku_id' => $account->acc_doku_id, 'email' => $account->acc_email]);
            $token = TH::build($token);

            if(empty($account->acc_doku_id))
            {
                $doku_id = $this->__getDoku($account);
                $statement = 'update tbl_account set acc_doku_id = :doku_id where acc_id = :acc_id';
                DB::update($statement,['doku_id' => $doku_id, 'acc_id' => $account->acc_id]);
                $account->acc_doku_id = $doku_id;
            }

            $this->data = [
                'id' => $account->acc_id,
                'email' => $account->acc_email,
                'signup_channel' => $account->acc_signup_channel,
                'mobile_number' => $account->acc_mobile_number,
                'country' => $account->acc_country,
                'doku_id' => $account->acc_doku_id,
                'token' => $token
            ];
        }
        else
        {
            $this->success = false;
            $this->message = 'Account Suspended';
        }
    }

    /**
     * @param $data instance Of Account
     * @return string
     */
    private function __getDoku($data)
    {
        return md5(time());
    }
}