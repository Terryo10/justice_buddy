<?php

namespace Database\Seeders;

use App\Models\LetterTemplate;
use Illuminate\Database\Seeder;

class LetterTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Eviction Notice Letter',
                'category' => 'eviction',
                'description' => 'A formal eviction notice letter for tenants who have breached their lease agreement.',
                'template_content' => <<<EOT
[Date]

{{tenant_name}}
{{tenant_address}}

Dear {{tenant_name}},

RE: NOTICE TO VACATE PREMISES - {{property_address}}

You are hereby notified that your tenancy of the above-described premises is hereby terminated effective {{eviction_date}}.

You are required to quit and surrender the premises to the undersigned on or before {{eviction_date}}, and in default thereof, legal proceedings will be instituted against you to recover possession of said premises, to declare the forfeiture of the lease or rental agreement under which you occupy said premises and to recover rents and damages, together with court costs and attorney's fees, according to the terms of your lease or rental agreement.

REASON FOR NOTICE:
{{reason_for_eviction}}

Amount owed (if applicable): {{amount_owed}}

Please be advised that you have the right to contest this notice in court. If you fail to vacate the premises by the specified date, eviction proceedings will be commenced against you.

Sincerely,

{{landlord_name}}
{{landlord_contact}}
EOT,
                'required_fields' => [
                    'tenant_name',
                    'tenant_address', 
                    'property_address',
                    'eviction_date',
                    'reason_for_eviction',
                    'landlord_name',
                    'landlord_contact'
                ],
                'optional_fields' => [
                    'amount_owed',
                    'lease_start_date',
                    'additional_notes'
                ],
                'ai_instructions' => 'Ensure the eviction notice follows South African rental laws and includes all required legal notices. The tone should be formal but not aggressive. Include proper legal disclaimers.',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Employment Termination Letter',
                'category' => 'employment',
                'description' => 'A formal letter for terminating an employee\'s contract.',
                'template_content' => <<<EOT
[Date]

{{employee_name}}
{{employee_address}}

Dear {{employee_name}},

RE: TERMINATION OF EMPLOYMENT CONTRACT

This letter serves as formal notice that your employment with {{company_name}} will be terminated effective {{termination_date}}.

REASON FOR TERMINATION:
{{termination_reason}}

NOTICE PERIOD:
You are entitled to {{notice_period}} notice as per your employment contract and the Basic Conditions of Employment Act.

FINAL PAYMENTS:
Your final salary payment will include:
- Salary up to {{termination_date}}
- Outstanding leave pay: {{leave_pay}}
- Any other applicable benefits

Please return all company property including:
- Company equipment
- Access cards/keys
- Confidential documents

We wish you well in your future endeavors.

Yours sincerely,

{{manager_name}}
{{manager_title}}
{{company_name}}
EOT,
                'required_fields' => [
                    'employee_name',
                    'employee_address',
                    'company_name',
                    'termination_date',
                    'termination_reason',
                    'notice_period',
                    'manager_name',
                    'manager_title'
                ],
                'optional_fields' => [
                    'leave_pay',
                    'severance_pay',
                    'employee_id',
                    'last_working_day'
                ],
                'ai_instructions' => 'Ensure compliance with South African labor laws including the Basic Conditions of Employment Act and Labour Relations Act. Include all required notice periods and benefits.',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Demand Letter for Payment',
                'category' => 'debt',
                'description' => 'A formal demand letter for outstanding payment or debt collection.',
                'template_content' => <<<EOT
[Date]

{{debtor_name}}
{{debtor_address}}

Dear {{debtor_name}},

RE: DEMAND FOR PAYMENT - ACCOUNT NUMBER: {{account_number}}

This letter serves as formal demand for payment of the outstanding amount of {{amount_owed}} which has been due since {{due_date}}.

DETAILS OF DEBT:
Description: {{debt_description}}
Original Amount: {{original_amount}}
Interest/Fees: {{interest_fees}}
Total Amount Due: {{total_amount_due}}

Despite previous reminders, this account remains unpaid. You are hereby given {{payment_deadline}} days from the date of this letter to settle this debt in full.

If payment is not received within the specified timeframe, we will have no alternative but to:
1. Hand over the matter to our attorneys for collection
2. Institute legal proceedings against you
3. Report the default to credit bureaus

Payment can be made to:
{{payment_details}}

Please contact us immediately if you wish to discuss payment arrangements.

Yours faithfully,

{{creditor_name}}
{{creditor_contact}}
EOT,
                'required_fields' => [
                    'debtor_name',
                    'debtor_address',
                    'amount_owed',
                    'due_date',
                    'debt_description',
                    'payment_deadline',
                    'creditor_name',
                    'creditor_contact'
                ],
                'optional_fields' => [
                    'account_number',
                    'original_amount',
                    'interest_fees',
                    'total_amount_due',
                    'payment_details',
                    'reference_number'
                ],
                'ai_instructions' => 'Follow South African debt collection laws and ensure the letter is firm but professional. Include proper payment instructions and legal disclaimers.',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Consumer Complaint Letter',
                'category' => 'consumer',
                'description' => 'A formal complaint letter for consumer rights violations.',
                'template_content' => <<<EOT
[Date]

{{company_name}}
{{company_address}}

Dear Sir/Madam,

RE: FORMAL COMPLAINT - {{complaint_reference}}

I am writing to formally complain about {{product_service}} that I purchased/received from your company on {{purchase_date}}.

DETAILS OF COMPLAINT:
{{complaint_details}}

ATTEMPTS TO RESOLVE:
{{previous_attempts}}

I am seeking the following resolution:
{{desired_resolution}}

According to the Consumer Protection Act 68 of 2008, I am entitled to:
- Goods/services of good quality
- Fair and honest dealing
- Accountability and transparency

Please respond to this complaint within 15 business days and provide a satisfactory resolution. Failure to do so will result in me reporting this matter to the National Consumer Commission.

I look forward to your prompt response.

Yours faithfully,

{{complainant_name}}
{{complainant_contact}}

Attachments: {{attachments}}
EOT,
                'required_fields' => [
                    'company_name',
                    'company_address',
                    'product_service',
                    'purchase_date',
                    'complaint_details',
                    'desired_resolution',
                    'complainant_name',
                    'complainant_contact'
                ],
                'optional_fields' => [
                    'complaint_reference',
                    'previous_attempts',
                    'attachments',
                    'invoice_number',
                    'receipt_number'
                ],
                'ai_instructions' => 'Reference the Consumer Protection Act and ensure the complaint is clear and factual. Include specific resolution requests and legal rights.',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Property Dispute Letter',
                'category' => 'property',
                'description' => 'A formal letter addressing property boundary or ownership disputes.',
                'template_content' => <<<EOT
[Date]

{{recipient_name}}
{{recipient_address}}

Dear {{recipient_name}},

RE: PROPERTY DISPUTE - {{property_address}}

This letter concerns the property dispute regarding {{property_description}} located at {{property_address}}.

NATURE OF DISPUTE:
{{dispute_details}}

EVIDENCE:
{{supporting_evidence}}

LEGAL BASIS:
{{legal_basis}}

We request that you:
{{requested_action}}

Please note that failure to respond satisfactorily within {{response_deadline}} days may result in us taking further legal action to protect our interests.

We remain open to amicable resolution of this matter and suggest {{proposed_solution}}.

Please confirm receipt of this letter and your intended course of action.

Yours sincerely,

{{sender_name}}
{{sender_contact}}
Legal Representative: {{legal_rep}} (if applicable)
EOT,
                'required_fields' => [
                    'recipient_name',
                    'recipient_address',
                    'property_address',
                    'property_description',
                    'dispute_details',
                    'requested_action',
                    'response_deadline',
                    'sender_name',
                    'sender_contact'
                ],
                'optional_fields' => [
                    'supporting_evidence',
                    'legal_basis',
                    'proposed_solution',
                    'legal_rep',
                    'survey_report',
                    'title_deed_reference'
                ],
                'ai_instructions' => 'Ensure the letter is legally sound and references applicable property laws. Maintain a professional tone while clearly stating the dispute and desired resolution.',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($templates as $template) {
            LetterTemplate::firstOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($template['name'])],
                $template
            );
        }
    }
}