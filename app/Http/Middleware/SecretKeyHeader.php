<?php
/**
 * User: febripratama
 * Date: 5/17/17
 * Time: 1:11 AM
 */

namespace App\Http\Middleware;

use App\Facades\SwirfFacade;
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use App\Helpers\ResponseHelper as RH;
use Illuminate\Support\Facades\DB;

class SecretKeyHeader
{
    public function handle($request, Closure $next)
    {
        $headers = $request->header();
        $checkHeader = $this->checkHeader($headers);
        if (!($checkHeader->success)) {
            return RH::errorResponse(RH::HTTP_BAD_REQUEST, $checkHeader->message);
        }

        return $next($request);
    }

    private function checkHeader($headers)
    {
        $result = array(
            'success' => true,
            'message' => null,
        );
        $required = array(
            'app-key' => 'App Key is Required',
            'app-secret' => 'App Secret is Required',
        );
        foreach ($required as $key => $message) {
            if (!(array_key_exists($key, $headers))) {
                $result['success'] = false;
                $result['message'] = $message;
            } else {
                if (!(isset($headers[$key]))) {
                    $result['success'] = false;
                    $result['message'] = $message;
                }
            }
        }

        if($result['success'] === true)
        {
            $statement = 'SELECT * FROM tbl_application WHERE app_key = :key AND app_secret = :secret limit 1';
            $app = DB::select($statement, ['key' => $headers['app-key'][0], 'secret' => $headers['app-secret'][0]]);
            if(count($app) == 0)
            {
                $result['success'] = false;
                $result['message'] = 'Wrong secret key/application not available';
            }
            else
            {
                \Swirf::setAppId($app[0]->app_uuid);
            }
        }

        return array2object($result);
    }
}