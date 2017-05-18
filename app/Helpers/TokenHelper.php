<?php


namespace App\Helpers;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
//use App\Helpers\ReturnDataHelper as RDH;

class TokenHelper
{
    public static function build($data)
    {
        if (is_array($data)) {
            $data = json_encode($data);
        }
        $signer = new Sha256();
        $token = (new Builder())->setIssuer(env('JWT_TOKEN_ISSUER')) // Configures the issuer (iss claim)
        ->setAudience(env('JWT_TOKEN_AUDIENCE')) // Configures the audience (aud claim)
        ->setId(env('JWT_TOKEN_ID'), true) // Configures the id (jti claim), replicating as a header item
        ->setIssuedAt(time()) // Configures the time that the token was issue (iat claim)
        ->setNotBefore(time()) // Configures the time that the token can be used (nbf claim)
        ->setExpiration(time() + 60 * 60 * 24 * 365 * 5) // Configures the expiration time of the token (exp claim)
        ->set('data', $data) // Configures a new claim, called "uid"
        ->sign($signer, env('JWT_TOKEN_SECRET')) // creates a signature using "testing" as key
        ->getToken(); // Retrieves the generated token
        return (string) $token;
    }

//    public static function validToken()
//    {
//        $result = new RDH();
//        if ($this->token == null) {
//            $result->setCode(ResponseHelper::HTTP_INVALID_TOKEN);
//            $result->setMessage('Token is required'); #TODO get it from translation
//        } else {
//            try {
//                $this->token = (new Parser())->parse((string) $this->token);
//                if (!($this->token == null)) {
//                    $data = new ValidationData();
//
//                    $data->setIssuer(env('JWT_TOKEN_ISSUER', CommonCons::TOKEN_ISSUER));
//                    $data->setAudience(env('JWT_TOKEN_AUDIENCE', CommonCons::TOKEN_AUDIENCE));
//                    $data->setId(env('JWT_TOKEN_ID', CommonCons::TOKEN_ID));
////                    if ($this->token->validate($data)) {
//                    if ($this->token->verify(new Sha256(), env('JWT_TOKEN_SECRET', CommonCons::TOKEN_SECRET))) {
//                        $data = $this->token->getClaim('data');
//                        try {
//                            if (startsWith($data, '{')) {
//                                $data = json_decode($data);
//                            } else {
//                                $data = Rijndael::decrypt($data);
//                                $data = json_decode($data);
//                            }
//                            if (!($data == false)) {
//                                if (property_exists($data, 'uid')) {
//                                    if (property_exists($data, 'device')) {
//                                        if (is_object($data->device)) {
//                                            $device = $data->device;
//                                            $this->device = $device->unique;
//                                            if (property_exists($data, 'version')) {
//                                                $this->version = $data->version;
//                                                $platform = explode('-', $this->version);
//                                                $platform = $platform[0];
//                                                if (strtolower($platform) == 'android')
//                                                {
//                                                    $this->platform = CommonCons::PLATFORM_ANDROID;
//                                                }
//                                                else
//                                                {
//                                                    $this->platform = CommonCons::PLATFORM_IOS;
//                                                }
//                                            }
//
//                                            $account = AH::getAccountByID($data->uid);
//                                            if ($account == null) {
//                                                $account = $this->__getAccountByID($data->uid);
//                                            }
//                                            if (!($account == null))
//                                            {
//                                                $next = true;
//                                                if (property_exists($account, 'acc_status'))
//                                                {
//                                                    if ((int)$account->acc_status == 0)
//                                                    {
//                                                        $next = false;
//                                                        $result->setCode(ResponseHelper::HTTP_INVALID_TOKEN);
//                                                        $result->setMessage(\Lang::get('error.account.suspended')); #TODO get it from translation
//                                                    }
//                                                }
//                                                if ($next)
//                                                {
//                                                    if (!($account == null)) {
//                                                        if (property_exists($account, 'public_key')) {
//                                                            $this->pubkey = $account->public_key;
//                                                        }
//                                                        $this->loggedIn = true;
//                                                        $account->dvc_id = $device->id;
//                                                        $account->adv_id = $device->account;
//                                                        $this->account = $account;
//                                                        $result->setSuccess(true);
//                                                    } else {
//                                                        $result->setCode(ResponseHelper::HTTP_INVALID_TOKEN);
//                                                        $result->setMessage(\Lang::get('error.account.logged_out')); #TODO get it from translation
//                                                    }
//                                                }
//                                            }
//                                            else
//                                            {
//                                                $result->setCode(ResponseHelper::HTTP_INVALID_TOKEN);
//                                                $result->setMessage(\Lang::get('error.account.logged_out')); #TODO get it from translation
//                                            }
//                                        } else {
//                                            $result->setCode(ResponseHelper::HTTP_INVALID_TOKEN);
//                                            $result->setMessage(\Lang::get('error.account.logged_out')); #TODO get it from translation
//                                        }
//                                    } else {
//                                        $result->setCode(ResponseHelper::HTTP_INVALID_TOKEN);
//                                        $result->setMessage(\Lang::get('error.account.logged_out')); #TODO get it from translation
//                                    }
//                                } else {
//                                    $result->setCode(ResponseHelper::HTTP_INVALID_TOKEN);
//                                    $result->setMessage(\Lang::get('error.account.logged_out')); #TODO get it from translation
//                                }
//                            } else {
//                                $result->setCode(ResponseHelper::HTTP_INVALID_TOKEN);
//                                $result->setMessage(\Lang::get('error.account.logged_out')); #TODO get it from translation
//                            }
//                        } catch (Exception $e) {
//                            $result->setCode(ResponseHelper::HTTP_INVALID_TOKEN);
//                            $result->setMessage($e->getMessage()); #TODO get it from translation
//                        }
//                    } else {
//                        $result->setCode(ResponseHelper::HTTP_INVALID_TOKEN);
//                        $result->setMessage(\Lang::get('error.account.logged_out')); #TODO get it from translation
//                    }
////                    } else {
////                        $result->setCode(ResponseHelper::HTTP_BAD_REQUEST);
////                        $result->setMessage("Hmmm...that's weird. You seem to have been logged out. Please login again."); #TODO get it from translation
////                    }
//                } else {
//                    $result->setCode(ResponseHelper::HTTP_INVALID_TOKEN);
//                    $result->setMessage(\Lang::get('error.account.logged_out')); #TODO get it from translation
//                }
//            } catch (\RuntimeException $e) {
//                $result->setCode(ResponseHelper::HTTP_INVALID_TOKEN);
//                $result->setMessage(\Lang::get('error.account.logged_out')); #TODO get it from translation
//            }
//        }
//
//        return $result;
//    }
}
