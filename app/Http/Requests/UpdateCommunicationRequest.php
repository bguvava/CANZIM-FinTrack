<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommunicationRequest extends FormRequest
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
            'type' => 'sometimes|required|in:Email,Phone Call,Meeting,Letter,Other',
            'communication_date' => 'sometimes|required|date|before_or_equal:today',
            'subject' => 'sometimes|required|string|max:255',
            'notes' => 'nullable|string',
            'next_action_date' => 'nullable|date|after_or_equal:today',
            'next_action_notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,txt,jpg,jpeg,png|max:5120',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Communication type is required.',
            'type.in' => 'Invalid communication type selected.',
            'communication_date.required' => 'Communication date is required.',
            'communication_date.date' => 'Invalid date format.',
            'communication_date.before_or_equal' => 'Communication date cannot be in the future.',
            'subject.required' => 'Subject is required.',
            'subject.max' => 'Subject cannot exceed 255 characters.',
            'next_action_date.after_or_equal' => 'Next action date cannot be in the past.',
            'attachment.file' => 'Attachment must be a valid file.',
            'attachment.mimes' => 'Attachment must be a PDF, DOC, DOCX, TXT, JPG, JPEG, or PNG file.',
            'attachment.max' => 'Attachment size cannot exceed 5MB.',
        ];
    }
}
