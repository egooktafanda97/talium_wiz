<?php

namespace TaliumAbstract\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestValidated extends FormRequest
{

    public bool $auth = true;
    public function __construct(protected $customRules)
    {
    }
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return  $this->customRules;
    }
}
