<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:5120'], // 5MB
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category' => ['required', 'string', 'in:budget-documents,expense-receipts,project-reports,donor-agreements,other'],
            'documentable_type' => ['required', 'string', 'in:App\\Models\\Project,App\\Models\\Budget,App\\Models\\Expense,App\\Models\\Donor'],
            'documentable_id' => ['required', 'integer', 'exists:'.$this->getTableName().',id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Please select a file to upload.',
            'file.mimes' => 'File must be PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, or PNG.',
            'file.max' => 'File size must not exceed 5MB.',
            'title.required' => 'Document title is required.',
            'category.required' => 'Please select a document category.',
            'category.in' => 'Invalid document category selected.',
        ];
    }

    /**
     * Get the table name based on documentable_type.
     */
    private function getTableName(): string
    {
        $types = [
            'App\\Models\\Project' => 'projects',
            'App\\Models\\Budget' => 'budgets',
            'App\\Models\\Expense' => 'expenses',
            'App\\Models\\Donor' => 'donors',
        ];

        return $types[$this->input('documentable_type')] ?? 'projects';
    }
}
