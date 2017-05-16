<?php

/*
 */

namespace App\Http\Controllers\V1;

use App\Helpers\TimeHelper as TH;


trait AppTrait
{
    public $success = false;
    public $message = null;
    public $data = null;
    public $code = 200;

    /**
     * @return Response
     */
    public function json()
    {
        $result = array();
        $result['success'] = $this->success;
        $result['message'] = $this->message;
        if (!($this->data == null)) {
            if (is_array($this->data)) {
                $this->data = valueArrayToValidType($this->data);
            }
            $result['data'] = $this->data;
        }
        $result['elapsed'] = TH::serverElapsedTime();
        return response()->json($result, $this->code);
    }
}