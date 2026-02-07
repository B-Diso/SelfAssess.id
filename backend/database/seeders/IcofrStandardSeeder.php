<?php

namespace Database\Seeders;

use App\Domain\Standard\Models\Standard;
use App\Domain\Standard\Models\StandardRequirement;
use App\Domain\Standard\Models\StandardSection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IcofrStandardSeeder extends Seeder
{
    /**
     * ICoFR Standard Structure
     */
    private array $standard = [
        'name' => 'ICoFR - Internal Control over Financial Reporting',
        'code' => 'ICoFR',
        'version' => '2024',
        'type' => 'financial',
        'description' => 'Internal control framework for financial reporting (ICoFR) based on COSO and SOX standards. This framework ensures the reliability of financial statements through a structured Risk Control Matrix (RCM) approach covering key business cycles.',
        'domains' => [
            [
                'code' => 'REV',
                'title' => 'Revenue & Receivables Cycle',
                'description' => 'Controls over Order-to-Cash process, including credit approval, billing, and revenue recognition.',
                'elements' => [
                    [
                        'title' => 'Revenue Recognition',
                        'description' => 'Accuracy and timing of revenue recording.',
                        'requirements' => [
                            ['code' => 'RC-01', 'title' => 'Revenue Cut-off and Timing Control', 'description' => 'Ensure revenue is recognized in the correct accounting period in accordance with ASC 606/IFRS 15.'],
                            ['code' => 'RC-02', 'title' => 'Revenue Accuracy and Pricing Authorization', 'description' => 'Ensure revenue is recorded at the correct amount based on authorized pricing and contracted terms.'],
                            ['code' => 'RC-03', 'title' => 'Returns, Allowances, and Credit Memo Control', 'description' => 'Ensure revenue is properly adjusted for returns, allowances, and credit memos in the correct period.'],
                            ['code' => 'RC-04', 'title' => 'Contract Review and Performance Obligation', 'description' => 'Ensure complex contracts are reviewed for proper revenue recognition treatment under ASC 606/IFRS 15.'],
                        ]
                    ],
                    [
                        'title' => 'Accounts Receivable',
                        'description' => 'Management of customer credit and collections.',
                        'requirements' => [
                            ['code' => 'AR-01', 'title' => 'Credit Approval and Management', 'description' => 'Ensure credit is extended only to creditworthy customers with appropriate approval and limits.'],
                            ['code' => 'AR-02', 'title' => 'Cash Receipts and Application Control', 'description' => 'Ensure cash receipts are properly recorded, deposited, and applied to customer accounts.'],
                            ['code' => 'AR-03', 'title' => 'AR Reconciliation and Subledger Integrity', 'description' => 'Ensure AR subledger accurately reconciles to the General Ledger and discrepancies are resolved promptly.'],
                            ['code' => 'AR-04', 'title' => 'Bad Debt Reserve and Write-off Control', 'description' => 'Ensure allowance for doubtful accounts is properly estimated and write-offs are appropriately authorized.'],
                            ['code' => 'AR-05', 'title' => 'Order Processing and Billing Control', 'description' => 'Ensure sales orders are properly authorized, processed, and billed accurately.'],
                        ]
                    ],
                ]
            ],
            [
                'code' => 'EXP',
                'title' => 'Procurement & Payables Cycle',
                'description' => 'Controls over Purchase-to-Pay process, including vendor management, three-way matching, and payment authorization.',
                'elements' => [
                    [
                        'title' => 'Procurement Authorization',
                        'description' => 'Verification of valid purchase requests.',
                        'requirements' => [
                            ['code' => 'EXP-01', 'title' => 'Purchase Requisition and Approval', 'description' => 'Ensure all procurement requests are properly authorized, budgeted, and approved before commitment.'],
                            ['code' => 'EXP-02', 'title' => 'Vendor Selection and Management', 'description' => 'Ensure vendors are properly vetted, approved, and monitored to prevent fraud and ensure compliance.'],
                            ['code' => 'EXP-03', 'title' => 'Purchase Order Authorization', 'description' => 'Ensure all purchase orders are properly authorized, documented, and issued only to approved vendors.'],
                            ['code' => 'EXP-08', 'title' => 'Vendor Master File Changes', 'description' => 'Ensure vendor master file changes are authorized, documented, and independently verified to prevent fraud.'],
                        ]
                    ],
                    [
                        'title' => 'Accounts Payable',
                        'description' => 'Timely and accurate payment to vendors.',
                        'requirements' => [
                            ['code' => 'EXP-04', 'title' => 'Three-Way Match Control', 'description' => 'Ensure payments are only made for goods/services received that match PO quantity, price, and terms.'],
                            ['code' => 'EXP-05', 'title' => 'Invoice Processing and Coding', 'description' => 'Ensure invoices are accurately coded, properly approved, and recorded in the correct accounting period.'],
                            ['code' => 'EXP-06', 'title' => 'Payment Authorization and Processing', 'description' => 'Ensure payments are authorized, accurate, and made only to valid vendors for received goods/services.'],
                            ['code' => 'EXP-07', 'title' => 'Expense Accruals', 'description' => 'Ensure all expenses incurred but not yet invoiced are properly accrued in the correct accounting period.'],
                        ]
                    ],
                ]
            ],
            [
                'code' => 'ITGC',
                'title' => 'IT General Controls',
                'description' => 'Controls over financial system access, change management, and IT operations.',
                'elements' => [
                    [
                        'title' => 'Access Management',
                        'description' => 'User identity and access control for ERP.',
                        'requirements' => [
                            ['code' => 'ITGC-01', 'title' => 'User Access Provisioning and Removal', 'description' => 'Ensure user access is granted only based on approved requests and promptly removed upon termination.'],
                            ['code' => 'ITGC-02', 'title' => 'Privileged Access Management', 'description' => 'Ensure privileged/administrative access is restricted, monitored, and approved by appropriate authority.'],
                            ['code' => 'ITGC-03', 'title' => 'Quarterly User Access Review', 'description' => 'Ensure user access rights are reviewed quarterly for appropriateness and compliance with least privilege.'],
                            ['code' => 'ITGC-04', 'title' => 'Segregation of Duties Enforcement', 'description' => 'Ensure incompatible duties are segregated and enforced through system configuration and monitoring.'],
                        ]
                    ],
                    [
                        'title' => 'Change Management',
                        'description' => 'Software development and environment changes.',
                        'requirements' => [
                            ['code' => 'ITGC-05', 'title' => 'Program Change Management', 'description' => 'Ensure all changes to production systems follow a formal SDLC process with testing and approval.'],
                            ['code' => 'ITGC-06', 'title' => 'Emergency Change Control', 'description' => 'Ensure emergency changes are documented, tested where possible, and properly authorized post-implementation.'],
                            ['code' => 'ITGC-07', 'title' => 'Developer Access to Production', 'description' => 'Ensure developers cannot directly modify production code without proper oversight and logging.'],
                        ]
                    ],
                    [
                        'title' => 'Computer Operations',
                        'description' => 'IT operations including backup, batch processing, and incident management.',
                        'requirements' => [
                            ['code' => 'ITGC-08', 'title' => 'Backup and Recovery Control', 'description' => 'Ensure critical financial data is backed up regularly and can be recovered in case of system failure.'],
                            ['code' => 'ITGC-09', 'title' => 'Job Scheduling and Monitoring', 'description' => 'Ensure batch jobs are scheduled properly, monitored for errors, and completed successfully.'],
                            ['code' => 'ITGC-10', 'title' => 'Problem and Incident Management', 'description' => 'Ensure IT incidents are logged, tracked, resolved, and root causes are addressed.'],
                        ]
                    ],
                ]
            ],
        ],
    ];

    public function run(): void
    {
        $this->command->info('📊 Seeding ICoFR Standard with detailed RCM...');

        DB::transaction(function () {
            $standard = Standard::firstOrCreate(
                [
                    'name' => $this->standard['name'],
                    'version' => $this->standard['version'],
                ],
                [
                    'type' => $this->standard['type'],
                    'description' => $this->standard['description'],
                    'is_active' => true,
                ]
            );

            if (!$standard->wasRecentlyCreated) {
                $this->command->warn("  ⚠️  ICoFR Standard already exists, skipping.");
                return;
            }

            foreach ($this->standard['domains'] as $domainData) {
                $domain = StandardSection::create([
                    'standard_id' => $standard->id,
                    'parent_id' => null,
                    'type' => 'domain',
                    'code' => $domainData['code'],
                    'title' => $domainData['title'],
                    'description' => $domainData['description'],
                ]);

                foreach ($domainData['elements'] as $idx => $elementData) {
                    $element = StandardSection::create([
                        'standard_id' => $standard->id,
                        'parent_id' => $domain->id,
                        'type' => 'element',
                        'code' => "{$domainData['code']}." . ($idx + 1),
                        'title' => $elementData['title'],
                        'description' => $elementData['description'],
                    ]);

                    foreach ($elementData['requirements'] as $reqData) {
                        StandardRequirement::create([
                            'standard_section_id' => $element->id,
                            'display_code' => $reqData['code'],
                            'title' => $reqData['title'],
                            'description' => $this->generateRcmDescription($reqData['code'], $reqData['title'], $reqData['description']),
                            'evidence_hint' => $this->generateEvidencePlan($reqData['code'], $reqData['title']),
                        ]);
                    }
                }
            }
        });

        $this->command->info('✅ ICoFR Standard seeded successfully!');
    }

    private function getCategoryFromCode(string $code): string
    {
        return match (true) {
            str_starts_with($code, 'RC-') => 'Revenue',
            str_starts_with($code, 'AR-') => 'Receivables',
            str_starts_with($code, 'EXP-') => 'Procurement',
            str_starts_with($code, 'ITGC-') => 'ITGC',
            default => 'General',
        };
    }

    private function getRcmData(string $category): array
    {
        return match ($category) {
            'Revenue' => [
                'principle' => 'P10 - Selects and develops control activities',
                'framework' => 'COSO Framework - Control Activities',
            ],
            'Receivables' => [
                'principle' => 'P10 - Selects and develops control activities',
                'framework' => 'COSO Framework - Control Activities',
            ],
            'Procurement' => [
                'principle' => 'P11 - Selects and develops general controls over technology',
                'framework' => 'COSO Framework - General Controls',
            ],
            'ITGC' => [
                'principle' => 'P11 - Selects and develops general controls over technology',
                'framework' => 'COSO Framework - IT General Controls',
            ],
            default => [
                'principle' => 'P10 - Selects and develops control activities',
                'framework' => 'COSO Framework',
            ],
        };
    }

    private function getControlFrequency(string $code): string
    {
        return match ($code) {
            'RC-01', 'RC-02', 'EXP-04', 'EXP-05', 'EXP-06' => 'Transactional',
            'RC-03', 'EXP-07' => 'Monthly',
            'RC-04' => 'Per Contract',
            'AR-01' => 'Per Customer',
            'AR-02' => 'Daily',
            'AR-03' => 'Monthly',
            'AR-04' => 'Quarterly',
            'AR-05' => 'Transactional',
            'EXP-01', 'EXP-03' => 'Per Transaction',
            'EXP-02' => 'Per Vendor',
            'EXP-08' => 'Per Change',
            'ITGC-01', 'ITGC-02' => 'Per Request',
            'ITGC-03' => 'Quarterly',
            'ITGC-04' => 'Quarterly',
            'ITGC-05', 'ITGC-06' => 'Per Change',
            'ITGC-07' => 'Monthly',
            'ITGC-08' => 'Daily',
            'ITGC-09' => 'Daily',
            'ITGC-10' => 'Per Incident',
            default => 'Periodic',
        };
    }

    private function getControlType(string $code): string
    {
        $automated = ['ITGC-01', 'ITGC-02', 'ITGC-04', 'ITGC-05', 'ITGC-07', 'ITGC-08', 'ITGC-09', 'EXP-04'];
        return in_array($code, $automated, true) ? 'Automated' : 'Manual';
    }

    private function getSpecificRisk(string $code, string $title): string
    {
        return match ($code) {
            'RC-01' => 'Revenue recorded in the incorrect accounting period (cut-off risk) leading to misstated financial results.',
            'RC-02' => 'Inaccurate billing due to incorrect pricing, unauthorized discounts, or discrepancies in quantities shipped vs. billed.',
            'RC-03' => 'Revenue not properly adjusted for returns, allowances, or credit memos, leading to overstated revenue.',
            'RC-04' => 'Complex contracts improperly assessed for revenue recognition treatment, resulting in non-compliance with ASC 606/IFRS 15.',
            'AR-01' => 'Credit extended to uncreditworthy customers resulting in uncollectible accounts and bad debt losses.',
            'AR-02' => 'Cash receipts not properly recorded, deposited, or applied to customer accounts, leading to misstated cash and AR.',
            'AR-03' => 'AR subledger does not reconcile to General Ledger, resulting in inaccurate financial reporting.',
            'AR-04' => 'Inadequate allowance for doubtful accounts or unauthorized write-offs, resulting in misstated AR valuation.',
            'AR-05' => 'Orders processed without proper authorization or billed inaccurately, resulting in revenue misstatement.',
            'EXP-01' => 'Unauthorized procurement activities leading to unapproved expenditures.',
            'EXP-02' => 'Vendors selected without proper vetting, resulting in fraud, quality issues, or unfavorable terms.',
            'EXP-03' => 'Purchase orders issued without proper authorization or to unapproved vendors.',
            'EXP-04' => 'Payments processed for goods/services not received or not meeting specifications.',
            'EXP-05' => 'Invoices inaccurately coded or recorded in the wrong accounting period.',
            'EXP-06' => 'Unauthorized or inaccurate payments processed, resulting in cash loss.',
            'EXP-07' => 'Expenses not properly accrued, resulting in understated liabilities and expenses.',
            'EXP-08' => 'Unauthorized changes to vendor master file, resulting in fraudulent payments.',
            'ITGC-01' => 'Unauthorized access granted or access not removed promptly upon termination, resulting in security breaches.',
            'ITGC-02' => 'Privileged access misused or monitored inadequately, resulting in unauthorized system changes.',
            'ITGC-03' => 'Inappropriate user access not identified and remediated, resulting in access violations.',
            'ITGC-04' => 'Segregation of duties violations not detected, resulting in fraud or errors.',
            'ITGC-05' => 'Untested or unauthorized changes deployed to production, resulting in system failures or data corruption.',
            'ITGC-06' => 'Emergency changes not properly documented or tested, resulting in system instability.',
            'ITGC-07' => 'Developers making unauthorized changes to production code, resulting in untested modifications.',
            'ITGC-08' => 'Data loss due to backup failures or inability to recover systems in case of disaster.',
            'ITGC-09' => 'Batch job failures not detected or resolved promptly, resulting in incomplete or inaccurate processing.',
            'ITGC-10' => 'IT incidents not properly tracked or resolved, resulting in recurring system issues.',
            default => "Failure to establish proper internal controls for {$title}.",
        };
    }

    private function getSpecificObjective(string $code, string $title): string
    {
        return match ($code) {
            'RC-01' => 'Verify that revenue transactions are recorded in the correct accounting period in accordance with applicable financial reporting standards.',
            'RC-02' => 'Guarantee that all billings are accurate, authorized, and based on approved price lists or contractual terms.',
            'RC-03' => 'Ensure revenue is properly adjusted for returns, allowances, and credit memos in the correct accounting period.',
            'RC-04' => 'Verify complex contracts are reviewed for proper identification of performance obligations and revenue recognition treatment.',
            'AR-01' => 'Ensure credit is extended only to creditworthy customers with appropriate approval and limits.',
            'AR-02' => 'Verify cash receipts are properly recorded, deposited, and applied to customer accounts accurately and timely.',
            'AR-03' => 'Ensure AR subledger accurately reconciles to the General Ledger and discrepancies are resolved promptly.',
            'AR-04' => 'Verify allowance for doubtful accounts is properly estimated and write-offs are appropriately authorized.',
            'AR-05' => 'Ensure sales orders are properly authorized, processed, and billed accurately and timely.',
            'EXP-01' => 'Ensure all procurement requests are properly authorized, budgeted, and approved before commitment.',
            'EXP-02' => 'Verify vendors are properly vetted, approved, and monitored to prevent fraud and ensure compliance.',
            'EXP-03' => 'Ensure all purchase orders are properly authorized, documented, and issued only to approved vendors.',
            'EXP-04' => 'Ensure payments are only made for goods/services received that match PO quantity, price, and terms.',
            'EXP-05' => 'Ensure invoices are accurately coded, properly approved, and recorded in the correct accounting period.',
            'EXP-06' => 'Ensure payments are authorized, accurate, and made only to valid vendors for received goods/services.',
            'EXP-07' => 'Ensure all expenses incurred but not yet invoiced are properly accrued in the correct accounting period.',
            'EXP-08' => 'Ensure vendor master file changes are authorized, documented, and independently verified to prevent fraud.',
            'ITGC-01' => 'Ensure user access is granted only based on approved requests and promptly removed upon termination.',
            'ITGC-02' => 'Ensure privileged/administrative access is restricted, monitored, and approved by appropriate authority.',
            'ITGC-03' => 'Ensure user access rights are reviewed quarterly for appropriateness and compliance with least privilege.',
            'ITGC-04' => 'Ensure incompatible duties are segregated and enforced through system configuration and monitoring.',
            'ITGC-05' => 'Ensure all changes to production systems follow a formal SDLC process with testing and approval.',
            'ITGC-06' => 'Ensure emergency changes are documented, tested where possible, and properly authorized post-implementation.',
            'ITGC-07' => 'Ensure developers cannot directly modify production code without proper oversight and logging.',
            'ITGC-08' => 'Ensure critical financial data is backed up regularly and can be recovered in case of system failure.',
            'ITGC-09' => 'Ensure batch jobs are scheduled properly, monitored for errors, and completed successfully.',
            'ITGC-10' => 'Ensure IT incidents are logged, tracked, resolved, and root causes are addressed.',
            default => "Ensure proper internal controls for {$title}.",
        };
    }

    private function getSpecificActivity(string $code, string $title): string
    {
        return match ($code) {
            'RC-01' => 'All sales orders are verified against signed customer contracts and shipping documents before revenue is recognized. Period-end cut-off procedures ensure revenue is recorded in the correct accounting period.',
            'RC-02' => 'Invoice prices are validated against approved price lists and customer contracts. Discounts greater than 5% require manager approval. Mathematical accuracy is verified by the system.',
            'RC-03' => 'Credit memos require dual approval (Sales Manager + Finance Manager) for amounts over $5,000. Returns are verified against receiving reports. Sales return reserves are reviewed quarterly.',
            'RC-04' => 'Complex contracts over $500,000 are reviewed by Revenue Accounting Specialist for proper identification of performance obligations and revenue recognition timing.',
            'AR-01' => 'Credit applications are reviewed with credit bureau reports and trade references. Credit limits are approved per authority matrix. Credit reviews are performed annually for high-exposure accounts.',
            'AR-02' => 'Cash receipts are deposited within 24 hours and applied to customer accounts per remittance advice. Lockbox services are utilized where applicable. Unapplied cash is resolved within 3 business days.',
            'AR-03' => 'Monthly reconciliation of AR subledger to General Ledger is performed by the 5th business day of the following month. All reconciling items are resolved within 30 days.',
            'AR-04' => 'Bad debt reserve is calculated quarterly using aging analysis and historical write-off rates. Write-offs over $25,000 require Controller approval; over $100,000 requires CFO approval.',
            'AR-05' => 'Orders are verified against customer POs and released from credit hold before processing. Three-way match (order, shipment, invoice) is enforced by the system.',
            'EXP-01' => 'Purchase requisitions require department head approval and budget verification. Requisitions over $50,000 require additional CFO approval.',
            'EXP-02' => 'New vendors require completed registration forms, KYC documentation, conflict of interest declarations, and dual approval (Procurement + Finance).',
            'EXP-03' => 'Purchase orders are system-generated with unique sequential numbers. POs over approval limits require additional authorization. Vendor status is validated before PO issuance.',
            'EXP-04' => 'System enforces three-way match (PO, receipt, invoice) before payment. Discrepancies over 1% tolerance trigger automatic payment block requiring manual override approval.',
            'EXP-05' => 'Invoices are matched to PO and receipt before processing. Non-PO invoices require additional approval. GL coding is verified for accuracy.',
            'EXP-06' => 'Payments over $25,000 require dual authorization. Wire transfers over $100,000 require CFO approval. Payee bank details are verified against vendor master file.',
            'EXP-07' => 'Month-end accruals are prepared based on receiving reports for un-invoiced receipts. Accruals are reviewed and approved by Controller.',
            'EXP-08' => 'Vendor master file changes require formal change request with supporting documentation and dual approval. Bank account changes are independently verified with vendor.',
            'ITGC-01' => 'User access requests require manager approval and role-based provisioning. Access is removed within 24 hours of termination notification.',
            'ITGC-02' => 'Privileged access is granted only with CIO approval. Privileged activities are logged and reviewed monthly. Emergency privileged access is time-limited.',
            'ITGC-03' => 'Quarterly User Access Review (UAR) is conducted by system owners to verify access appropriateness. Exceptions are documented and remediated within 30 days.',
            'ITGC-04' => 'System enforces segregation of duties through role configuration. Quarterly SOD reports are reviewed for violations.',
            'ITGC-05' => 'All production changes require documented testing, UAT sign-off, and CAB approval. Changes are deployed through controlled deployment windows.',
            'ITGC-06' => 'Emergency changes require verbal approval followed by documented post-implementation review within 48 hours. All emergency changes are reported to CAB.',
            'ITGC-07' => 'Developers do not have direct access to modify production code. All code changes must be deployed through the approved CI/CD pipeline.',
            'ITGC-08' => 'Daily incremental and weekly full backups are performed. Backup logs are reviewed daily. Semi-annual restore testing verifies recovery capability.',
            'ITGC-09' => 'Critical batch jobs are scheduled through job scheduler with automated monitoring. Job failures trigger immediate alerts to operations team.',
            'ITGC-10' => 'All IT incidents are logged in the service management system. Critical incidents are escalated and resolved within defined SLAs. Root cause analysis is performed for high-impact incidents.',
            default => "Management shall implement formal review, approval, and monitoring procedures for {$title}.",
        };
    }

    private function generateRcmDescription(string $code, string $title, string $description): string
    {
        $category = $this->getCategoryFromCode($code);
        $rcmData = $this->getRcmData($category);
        $risk = $this->getSpecificRisk($code, $title);
        $objective = $this->getSpecificObjective($code, $title);
        $activity = $this->getSpecificActivity($code, $title);
        $frequency = $this->getControlFrequency($code);
        $type = $this->getControlType($code);
        $level = str_starts_with($code, 'ITGC') ? 'IT General Control' : 'Key Control';

        return "<h3>Risk Control Matrix (RCM) Detail</h3>" .
               "<p><strong>{$rcmData['framework']}</strong><br />" .
               "<strong>Principle:</strong> {$rcmData['principle']}</p>" .
               "<ul>" .
               "<li><strong>Risk:</strong> {$risk}</li>" .
               "<li><strong>Control Objective:</strong> {$objective}</li>" .
               "<li><strong>Control Activity:</strong> {$activity}</li>" .
               "</ul>" .
               "<hr />" .
               "<p><strong>Control Metadata:</strong></p>" .
               "<ul>" .
               "<li><strong>Frequency:</strong> {$frequency}</li>" .
               "<li><strong>Control Type:</strong> {$type}</li>" .
               "<li><strong>Importance Level:</strong> <span class='badge'>{$level}</span></li>" .
               "</ul>";
    }

    private function generateEvidencePlan(string $code, string $title): string
    {
        if (str_starts_with($code, 'ITGC-')) {
            return $this->getItgcEvidence($code);
        }
        if (str_starts_with($code, 'RC-')) {
            return $this->getRevenueEvidence($code);
        }
        if (str_starts_with($code, 'AR-')) {
            return $this->getReceivablesEvidence($code);
        }
        if (str_starts_with($code, 'EXP-')) {
            return $this->getProcurementEvidence($code);
        }
        return $this->getDefaultEvidence($code, $title);
    }

    private function getItgcEvidence(string $code): string
    {
        $plans = [
            'ITGC-01' => [
                'title' => 'User Access Provisioning and Removal',
                'objective' => 'Ensure user access is granted only based on approved requests and promptly removed upon termination.',
                'documents' => 'User Access Request forms with manager approval; New hire onboarding checklists; Termination notifications from HR; System access provisioning logs',
                'procedures' => 'Provisioning Verification: Verify access request has proper manager approval; Removal Verification: Compare HR termination date to access removal date',
            ],
            'ITGC-02' => [
                'title' => 'Privileged Access Management',
                'objective' => 'Ensure privileged/administrative access is restricted, monitored, and approved by appropriate authority.',
                'documents' => 'Privileged access request forms with CIO approval; Privileged access user list; Privileged access activity logs; Quarterly privileged access review documentation',
                'procedures' => 'Access Authorization: Verify each privileged user has documented business justification; Activity Review: Review privileged activity logs for unusual activity',
            ],
            'ITGC-03' => [
                'title' => 'Quarterly User Access Review',
                'objective' => 'Ensure user access rights are reviewed quarterly for appropriateness and compliance with least privilege.',
                'documents' => 'Quarterly User Access Review reports; System owner sign-off documentation; Exception reports for access removals; Remediation tracking',
                'procedures' => 'Review Completeness: Verify UAR was completed for all financial systems; Remediation Review: Verify exceptions were remediated within 30 days',
            ],
            'ITGC-04' => [
                'title' => 'Segregation of Duties Enforcement',
                'objective' => 'Ensure incompatible duties are segregated and enforced through system configuration and monitoring.',
                'documents' => 'SOD conflict reports from GRC/audit tools; SOD rule matrix documentation; Remediation plans for SOD violations; Compensating control documentation',
                'procedures' => 'SOD Analysis: Review SOD conflict report for all financial users; Compensating Controls: For unavoidable conflicts, verify compensating controls exist',
            ],
            'ITGC-05' => [
                'title' => 'Program Change Management',
                'objective' => 'Ensure all changes to production systems follow a formal SDLC process with testing and approval.',
                'documents' => 'Change requests with business justification; Technical design specifications; Test plans and test results; CAB approval minutes; Deployment logs',
                'procedures' => 'Change Request Review: Verify change request has documented business justification; Testing Verification: Verify test plans exist and are approved',
            ],
            'ITGC-06' => [
                'title' => 'Emergency Change Control',
                'objective' => 'Ensure emergency changes are documented, tested where possible, and properly authorized post-implementation.',
                'documents' => 'Emergency change requests with justification; Verbal approval evidence; Post-implementation review documentation; Retrospective testing results',
                'procedures' => 'Emergency Justification: Verify business justification for emergency classification; Post-Implementation: Verify post-implementation review completed within 48 hours',
            ],
            'ITGC-07' => [
                'title' => 'Developer Access to Production',
                'objective' => 'Ensure developers cannot directly modify production code without proper oversight and logging.',
                'documents' => 'Developer access matrix showing environment permissions; CI/CD pipeline configuration; Production deployment logs; Production change audit logs',
                'procedures' => 'Access Review: Verify developers do not have direct production write access; Deployment Verification: Verify all changes deployed through CI/CD pipeline',
            ],
            'ITGC-08' => [
                'title' => 'Backup and Recovery Control',
                'objective' => 'Ensure critical financial data is backed up regularly and can be recovered in case of system failure.',
                'documents' => 'Backup schedule and configuration; Daily backup logs; Restore test plans and results; RTO and RPO documentation',
                'procedures' => 'Backup Completeness: Verify all financial databases are included in backup schedule; Restore Testing: Verify semi-annual restore test was performed',
            ],
            'ITGC-09' => [
                'title' => 'Job Scheduling and Monitoring',
                'objective' => 'Ensure batch jobs are scheduled properly, monitored for errors, and completed successfully.',
                'documents' => 'Job schedule documentation; Daily job completion logs; Job failure alerts and notifications; Error resolution documentation',
                'procedures' => 'Schedule Verification: Verify critical jobs are scheduled per requirements; Error Handling: Verify errors were resolved and jobs re-run successfully',
            ],
            'ITGC-10' => [
                'title' => 'Problem and Incident Management',
                'objective' => 'Ensure IT incidents are logged, tracked, resolved, and root causes are addressed.',
                'documents' => 'Incident tickets from ITSM system; Incident categorization; Resolution documentation; Problem records for recurring issues; Root cause analysis reports',
                'procedures' => 'Logging Verification: Verify all incidents were logged in ITSM system; SLA Compliance: Review resolution times against SLA targets',
            ],
        ];

        $plan = $plans[$code] ?? ['title' => $code, 'objective' => 'Control objective', 'documents' => 'Documentation required', 'procedures' => 'Test procedures'];

        return "<h3>🔍 ITGC Evidence Plan: {$plan['title']}</h3>" .
               "<p><strong>Control Objective:</strong> {$plan['objective']}</p>" .
               "<h4>📄 Required Documents</h4><ul><li>" . str_replace('; ', '</li><li>', $plan['documents']) . "</li></ul>" .
               "<h4>🧪 Test Procedures</h4><ul><li>" . str_replace('; ', '</li><li>', $plan['procedures']) . "</li></ul>";
    }

    private function getRevenueEvidence(string $code): string
    {
        $plans = [
            'RC-01' => ['title' => 'Revenue Cut-off and Timing Control', 'sample' => '25 transactions (15 pre-year-end + 10 post-year-end)', 'documents' => 'Sales invoices with shipping documents; Customer acceptance confirmations; Revenue recognition checklists; Period-end cut-off memo'],
            'RC-02' => ['title' => 'Revenue Accuracy and Pricing Authorization', 'sample' => '30 transactions stratified by value', 'documents' => 'Sales invoices with detailed pricing; Approved price lists; Customer contracts; Discount authorization forms'],
            'RC-03' => ['title' => 'Returns, Allowances, and Credit Memo Control', 'sample' => 'All credit memos > $10,000 + random sample of 20', 'documents' => 'Credit memo requests; RMA forms; Receiving reports for returns; Original sales invoices; Sales allowance reserve calculations'],
            'RC-04' => ['title' => 'Contract Review and Performance Obligation', 'sample' => 'All contracts > $500,000 + 10 random $100K-$500K', 'documents' => 'Executed customer contracts; Revenue recognition checklists; SSP analysis documentation; Performance obligation evaluation memos'],
        ];

        $plan = $plans[$code] ?? ['title' => $code, 'sample' => 'Sample of transactions', 'documents' => 'Relevant documentation'];

        return "<h3>🔍 Revenue Evidence Plan: {$plan['title']}</h3>" .
               "<p><strong>COSO Framework:</strong> Control Activities | <strong>SOX Compliance:</strong> 404 Assessment Required</p>" .
               "<h4>📋 Sampling Methodology</h4><p>{$plan['sample']}</p>" .
               "<h4>📄 Required Documents</h4><ul><li>" . str_replace('; ', '</li><li>', $plan['documents']) . "</li></ul>";
    }

    private function getReceivablesEvidence(string $code): string
    {
        $plans = [
            'AR-01' => ['title' => 'Credit Approval and Management', 'sample' => '25 new credit approvals', 'documents' => 'Credit applications; Credit bureau reports; Credit approval workflow; Credit limit authorization matrix; Trade reference verification'],
            'AR-02' => ['title' => 'Cash Receipts and Application Control', 'sample' => '30 cash receipts', 'documents' => 'Bank deposit slips; Lockbox reports; AR subledger application reports; Cash receipts journal; Customer remittance advices'],
            'AR-03' => ['title' => 'AR Reconciliation and Subledger Integrity', 'sample' => 'All 12 month-end reconciliations', 'documents' => 'Monthly AR to GL reconciliation worksheets; Supporting detail for reconciling items; Journal entries; Management sign-off; AR aging reports'],
            'AR-04' => ['title' => 'Bad Debt Reserve and Write-off Control', 'sample' => 'All quarterly reserve calculations; All write-offs > $25,000', 'documents' => 'Bad debt reserve calculation worksheets; Historical write-off analysis; Specific reserve analysis; Write-off approval forms; Collection effort documentation'],
            'AR-05' => ['title' => 'Order Processing and Billing Control', 'sample' => '30 sales orders', 'documents' => 'Sales orders with customer POs; Credit hold/release documentation; Picking/packing slips; Shipping documents; Sales invoices'],
        ];

        $plan = $plans[$code] ?? ['title' => $code, 'sample' => 'Sample of transactions', 'documents' => 'Relevant documentation'];

        return "<h3>🔍 Receivables Evidence Plan: {$plan['title']}</h3>" .
               "<p><strong>COSO Framework:</strong> Control Activities, Monitoring | <strong>SOX Compliance:</strong> 404 Assessment Required</p>" .
               "<h4>📋 Sampling Methodology</h4><p>{$plan['sample']}</p>" .
               "<h4>📄 Required Documents</h4><ul><li>" . str_replace('; ', '</li><li>', $plan['documents']) . "</li></ul>";
    }

    private function getProcurementEvidence(string $code): string
    {
        $plans = [
            'EXP-01' => ['title' => 'Purchase Requisition and Approval', 'sample' => '30 requisitions stratified by value', 'documents' => 'Purchase Requisition forms; Approved vendor quotations; Business justification; Budget approval evidence; System audit trail'],
            'EXP-02' => ['title' => 'Vendor Selection and Management', 'sample' => '25 vendor onboarding files', 'documents' => 'Vendor Registration Forms; KYC documentation; Signed Vendor Agreements; Conflict of Interest declarations; Background check reports'],
            'EXP-03' => ['title' => 'Purchase Order Authorization', 'sample' => '40 purchase orders', 'documents' => 'Official Purchase Orders; Approved Purchase Requisitions; Vendor quotations; PO approval workflow evidence; Terms acceptance'],
            'EXP-04' => ['title' => 'Three-Way Match Control', 'sample' => '45 invoices stratified by value', 'documents' => 'Purchase Orders; Goods Receipt Notes; Vendor Invoices; Three-Way Match Exception Reports; Override approvals'],
            'EXP-05' => ['title' => 'Invoice Processing and Coding', 'sample' => '35 invoices', 'documents' => 'Original vendor invoices; Invoice processing checklists; GL coding approval; Cost center allocations; Period-end cutoff documentation'],
            'EXP-06' => ['title' => 'Payment Authorization and Processing', 'sample' => '40 payment transactions', 'documents' => 'Payment vouchers; Payment run reports; Supporting invoices; Bank account verification; Dual authorization evidence'],
            'EXP-07' => ['title' => 'Expense Accruals', 'sample' => '30 accrual entries', 'documents' => 'Accrual support worksheets; Receiving reports; Usage reports; Contracts/agreements; Accrual approval forms'],
            'EXP-08' => ['title' => 'Vendor Master File Changes', 'sample' => '35 vendor master file changes', 'documents' => 'Vendor Master File Change Request Forms; Supporting documentation; Dual approval evidence; System change logs; Independent verification'],
        ];

        $plan = $plans[$code] ?? ['title' => $code, 'sample' => 'Sample of transactions', 'documents' => 'Relevant documentation'];

        return "<h3>🔍 Procurement Evidence Plan: {$plan['title']}</h3>" .
               "<p><strong>COSO Framework:</strong> Control Activities | <strong>SOX Relevance:</strong> High</p>" .
               "<h4>📋 Sampling Methodology</h4><p>{$plan['sample']}</p>" .
               "<h4>📄 Required Documents</h4><ul><li>" . str_replace('; ', '</li><li>', $plan['documents']) . "</li></ul>";
    }

    private function getDefaultEvidence(string $code, string $title): string
    {
        return "<h3>🔍 Evidence Plan: {$code} - {$title}</h3>" .
               "<p><strong>SOX Relevance:</strong> Key Control</p>" .
               "<h4>Required Evidence Documentation</h4>" .
               "<ul>" .
               "<li>Documented Standard Operating Procedures (SOP) for this process</li>" .
               "<li>Evidence of management review and approval</li>" .
               "<li>Periodic reconciliation or monitoring reports</li>" .
               "</ul>" .
               "<p><em>Note: Ensure all documents are properly dated and signed.</em></p>";
    }
}
