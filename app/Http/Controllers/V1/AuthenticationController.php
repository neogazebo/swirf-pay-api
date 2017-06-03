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

    public function signin(Request $request){

        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'phone_number' => 'required',
            'password' => 'required',
            'country' => 'required',
        ]);

        if (!($validator->fails()))
        {
            $account = $this->__signinCheckDB($request, self::LOGIN_VIA_EMAIL);
            if($account === null)
            {
                $account = $this->__signup($request, self::LOGIN_VIA_EMAIL);
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

    public function signinGoogle(Request $request){


        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'phone_number' => 'required',
            'country' => 'required',
            'google.id' => 'required'
        ]);

        if (!($validator->fails()))
        {
            $account = $this->__signinCheckDB($request, self::LOGIN_VIA_GOOGLE);
            if($account === null)
            {
                $account = $this->__signup($request, self::LOGIN_VIA_GOOGLE);
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
                $this->__afterLogin($account, self::LOGIN_VIA_GOOGLE);
            }
        }
        else
        {
            $this->code = RH::HTTP_BAD_REQUEST;
            $this->message = $validator->errors()->all()[0];
        }

        return $this->json();
    }

    private function __signinCheckDB(Request $request, $via = self::LOGIN_VIA_EMAIL)
    {
        if($via == self::LOGIN_VIA_EMAIL)
        {
            $statement = 'select * from tbl_account where acc_email = :email and acc_app_uuid = :app_id  limit 0,1';
            $account = DB::select($statement, ['email' => $request->input('email'), 'app_id' => \Swirf::getAppId()]);
            if(count($account) == 1)
            {
                if (app('hash')->check($request->input('password'), $account[0]->acc_password)) {
                    return $account[0];
                }
                return false;
            }
        }
        elseif ($via == self::LOGIN_VIA_GOOGLE)
        {
            $statement = 'select * from tbl_account where acc_google_id = :google_id and acc_app_uuid = :app_id  limit 0,1';
            $account = DB::select($statement, ['google_id' => $request->input('google.id'), 'app_id' => \Swirf::getAppId()]);
            if(count($account) == 1)
            {
                return $account[0];
            }
        }
        return null;
        // todo create condition for via Google
    }

    /**
     * @param $request
     * @param $via
     * @return bool
     */
    private function __signup(Request $request, $via = self::LOGIN_VIA_EMAIL)
    {
        $arrInsert = [
            'acc_email' => $request->input('email'),
            'acc_signup_channel' => $via,
            'acc_mobile_number' => $request->input('phone_number'),
            'acc_country' => $request->input('country'),
            'acc_app_uuid' => \Swirf::getAppId(),
            'acc_created_at' => time()
        ];

        if($via == self::LOGIN_VIA_EMAIL)
        {
            $arrInsert['acc_password'] = app('hash')->make($request->input('password'));
        }
        elseif ($via == self::LOGIN_VIA_GOOGLE)
        {
            $arrInsert['acc_google_id'] = $request->input('google.id');
        }

        $id = DB::table('tbl_account')->insertGetId($arrInsert);

        if(!empty($id))
        {
            $account = DB::select("select * from tbl_account where acc_id = {$id} limit 0,1");
            return $account[0];
        }
        return false;
    }

    /**
     * @param $account instance of Account
     * @param $via instance of Account
     */
    private function __afterLogin($account, $via = self::LOGIN_VIA_EMAIL)
    {
        if($account->acc_status == self::ACCOUNT_ACTIVE)
        {
            $token = json_encode(['account_id' => $account->acc_id, 'doku_id' => $account->acc_doku_id, 'email' => $account->acc_email, 'is_active' => $account->acc_status]);
            $token = TH::build($token);

            if(empty($account->acc_doku_id))
            {
                $doku_id = $this->__getDoku($account);
                $statement = 'update tbl_account set acc_doku_id = :doku_id where acc_id = :acc_id';
                DB::update($statement,['doku_id' => $doku_id, 'acc_id' => $account->acc_id]);
                $account->acc_doku_id = $doku_id;
            }

            $arrResult = [
                'id' => $account->acc_id,
                'email' => $account->acc_email,
                'signup_channel' => $account->acc_signup_channel,
                'mobile_number' => $account->acc_mobile_number,
                'country' => $account->acc_country,
                'doku_id' => $account->acc_doku_id,
                'token' => $token
            ];

            if($via == self::LOGIN_VIA_GOOGLE)
            {
                $arrResult['google_id'] = $account->acc_google_id;
            }
            $this->data = $arrResult;

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