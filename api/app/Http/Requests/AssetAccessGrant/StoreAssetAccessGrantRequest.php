<?php

namespace App\Http\Requests\AssetAccessGrant;

use App\Enums\AssetAccessRole;
use App\Models\Asset;
use App\Rules\AssetAccessCompositeUnique;
use App\Rules\UserExistedInOrg;
use App\Rules\UserGroupExistedInOrg;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreAssetAccessGrantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role' => ['bail', 'required', new Enum(AssetAccessRole::class)],
            'user_ids' => [
                'required_without:user_group_ids',
                'array',
            ],
            'user_ids.*' => [
                'integer',
            ],
            'user_group_ids' => [
                'required_without:user_ids',
                'array',
            ],
            'user_group_ids.*' => [
                'integer',
            ],
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->has('role')) {
                return;
            }
            $asset = $this->route('asset');
            $this->validateUserAccess($validator, $asset);
            $this->validateUserGroupAccess($validator, $asset);
        });
    }

    public function validateUserAccess(Validator $validator, Asset $asset)
    {
        if (!$this->has('user_ids') || !is_array($this->user_ids)) {
            return;
        }
        foreach ($this->user_ids as $index => $userId) {
            $attribute = "user_ids.{$index}";
            $compositeUniqueRule = new AssetAccessCompositeUnique(
                'asset_access_grants',
                'user_id',
                $this->role,
                $asset->id,
            );
            $compositeUniqueRule->validate(
                $attribute,
                $userId,
                function ($message) use ($validator, $attribute) {
                    $validator->errors()->add($attribute, $message);
                }
            );

            $userExistedInOrgRule = new UserExistedInOrg($asset->org_id);
            $userExistedInOrgRule->validate(
                $attribute,
                $userId,
                function ($message) use ($validator, $attribute) {
                    $validator->errors()->add($attribute, $message);
                }
            );
        }
    }

    protected function validateUserGroupAccess(Validator $validator, Asset $asset)
    {
        if (!$this->has('user_group_ids') || !is_array($this->user_group_ids)) {
            return;
        }
        foreach ($this->user_group_ids as $index => $userGroupId) {
            $attribute = "user_group_ids.{$index}";
            $compositeUniqueRule = new AssetAccessCompositeUnique(
                'asset_access_grants',
                'user_group_id',
                $this->role,
                $asset->id,
            );
            $compositeUniqueRule->validate(
                $attribute,
                $userGroupId,
                function ($message) use ($validator, $attribute) {
                    $validator->errors()->add($attribute, $message);
                }
            );

            $userGroupExistedInOrgRule = new UserGroupExistedInOrg($asset->org_id);
            $userGroupExistedInOrgRule->validate(
                $attribute,
                $userGroupId,
                function ($message) use ($validator, $attribute) {
                    $validator->errors()->add($attribute, $message);
                }
            );
        }
    }

    public function messages(): array
    {
        return [
            'role.required' => 'Role is required.',
            'user_ids.required_without' => 'Either users or user groups must be provided.',
            'user_ids.array' => 'User IDs must be an array.',
            'user_ids.min' => 'At least one user must be selected.',
            'user_ids.*.integer' => 'Invalid user ID.',
            'user_group_ids.required_without' => 'Either users or user groups must be provided.',
            'user_group_ids.array' => 'User group IDs must be an array.',
            'user_group_ids.min' => 'At least one user group must be selected.',
            'user_group_ids.*.integer' => 'Invalid user group ID.',
        ];
    }

    public function attributes(): array
    {
        return [
            'user_id' => 'User',
            'user_group_id' => 'User Group',
            'role' => 'Access Role',
        ];
    }
}
