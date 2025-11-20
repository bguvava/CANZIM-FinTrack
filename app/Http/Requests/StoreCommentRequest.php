<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
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
            'commentable_type' => ['required', 'string'],
            'commentable_id' => ['required', 'integer'],
            'content' => ['required', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'exists:comments,id'],
            'attachments' => ['nullable', 'array', 'max:3'],
            'attachments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'commentable_type.required' => 'The commentable type is required',
            'commentable_type.string' => 'The commentable type must be a valid string',
            'commentable_id.required' => 'The commentable ID is required',
            'commentable_id.integer' => 'The commentable ID must be a valid integer',
            'content.required' => 'Comment content is required',
            'content.string' => 'Comment content must be a valid string',
            'content.max' => 'Comment content cannot exceed 1000 characters',
            'parent_id.exists' => 'The parent comment does not exist',
            'attachments.array' => 'Attachments must be provided as an array',
            'attachments.max' => 'You can upload a maximum of 3 attachments',
            'attachments.*.file' => 'Each attachment must be a valid file',
            'attachments.*.mimes' => 'Attachments must be PDF, JPG, JPEG, or PNG files',
            'attachments.*.max' => 'Each attachment must not exceed 2MB in size',
        ];
    }
}
