<?php

namespace App;

use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

trait VerifyRequestInput
{
    /**
     * @param $key
     * @param  null  $default
     * @return mixed
     * @throws BusinessException
     */
    public function verifyId($key, $default = null)
    {
        return $this->verifyData($key, $default, 'integer|digits_between:1,20|min:1');
    }

    /**
     * @param $key
     * @param  null  $default
     * @return mixed
     * @throws BusinessException
     */
    public function verifyString($key, $default = null)
    {
        return $this->verifyData($key, $default, 'string');
    }

    /**
     * @param $key
     * @param  null  $default
     * @return mixed
     * @throws BusinessException
     */
    public function verifyBoolean($key, $default = null)
    {
        return $this->verifyData($key, $default, 'boolean');
    }

    /**
     * @param $key
     * @param  null  $default
     * @return mixed
     * @throws BusinessException
     */
    public function verifyInteger($key, $default = null)
    {
        return $this->verifyData($key, $default, 'integer');
    }

    /**
     * @param $key
     * @param  null  $default
     * @return mixed
     * @throws BusinessException
     */
    public function verifyPositiveInteger($key, $default = null)
    {
        return $this->verifyData($key, $default, 'integer|min:1');
    }


    /**
     * @param $key
     * @param  null  $default
     * @param  array  $enum
     * @return mixed
     * @throws BusinessException
     */
    public function verifyEnum($key, $default = null, $enum = [])
    {
        return $this->verifyData($key, $default, Rule::in($enum));
    }

    /**
     * @param $key
     * @param  null  $default
     * @return mixed
     * @throws BusinessException
     */
    public function verifyArrayNotEmpty($key, $default = null)
    {
        return $this->verifyData($key, $default, 'array|min:1');
    }


    /**
     * 手机号验证
     * @param $key
     * @param  null  $default
     * @return mixed
     * @throws BusinessException
     */
    public function verifyMobile($key, $default = null)
    {
        return $this->verifyData($key, $default, 'regex:/^1[0-9]{10}$/', CodeResponse::AUTH_INVALID_MOBILE);
    }


    /**
     * @param $key
     * @param $default
     * @param $rule
     * @param  array  $codeResponse
     * @return mixed
     * @throws BusinessException
     */
    public function verifyData($key, $default, $rule, $codeResponse = CodeResponse::PARAM_VALUE_ILLEGAL)
    {
        $value = request()->input($key, $default);

        if (is_null($default) && is_null($value)) {
            return $value;
        }

        $this->check([$key => $value], [$key => $rule], $codeResponse);

        return $value;
    }

    /**
     * @param $data
     * @param $rule
     * @param  array  $codeResponse
     * @throws BusinessException
     */
    public function check($data, $rule, $codeResponse = CodeResponse::PARAM_VALUE_ILLEGAL)
    {
        $validator = Validator::make($data, $rule);
        if ($validator->fails()) {
            if (app()->environment('production')) {
                throw new BusinessException($codeResponse);
            }
            throw new BusinessException($codeResponse, ($codeResponse[1] ?? '').':'.$validator->errors()->toJson());
        }
    }
}