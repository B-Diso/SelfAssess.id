<?php

namespace Database\Seeders\ICoFR;

/**
 * ICoFR Standard Data Structure
 * Contains domains, elements, and requirements definitions
 */
class IcofrData
{
    /**
     * ICoFR Standard Structure - Focused on REV, EXP, ITGC only
     */
    public static array $standard = [
        'name' => 'ICoFR - Internal Control over Financial Reporting',
        'code' => 'ICoFR',
        'version' => '2024',
        'type' => 'financial',
        'description' => 'Internal control framework for financial reporting (ICoFR) based on COSO and SOX standards. This framework ensures the reliability of financial statements through a structured Risk Control Matrix (RCM) approach covering Revenue, Expense, and IT General Controls.',
        'period_type' => 'semester',
        'domains' => [
            [
                'code' => 'REV',
                'title' => 'Revenue & Receivables Cycle',
                'description' => 'Controls over Order-to-Cash process, including revenue recognition, credit management, billing, and cash receipts.',
                'elements' => [
                    [
                        'title' => 'Revenue Recognition',
                        'description' => 'Accuracy and timing of revenue recording.',
                        'requirements' => [
                            ['code' => 'RC-01', 'title' => 'Revenue Cut-off and Timing Control', 'description' => 'Ensure revenue is recognized in the correct accounting period.'],
                            ['code' => 'RC-02', 'title' => 'Revenue Accuracy and Pricing Authorization', 'description' => 'Ensure revenue is recorded at correct amounts with proper authorization.'],
                            ['code' => 'RC-03', 'title' => 'Returns, Allowances, and Credit Memo Control', 'description' => 'Ensure proper handling of returns and credit memos.'],
                            ['code' => 'RC-04', 'title' => 'Contract Review and Performance Obligation', 'description' => 'Ensure complex contracts are reviewed for proper revenue recognition.'],
                        ]
                    ],
                    [
                        'title' => 'Accounts Receivable',
                        'description' => 'Management of customer credit and collections.',
                        'requirements' => [
                            ['code' => 'AR-01', 'title' => 'Credit Approval and Management', 'description' => 'Ensure credit is extended only to creditworthy customers.'],
                            ['code' => 'AR-02', 'title' => 'Cash Receipts and Application Control', 'description' => 'Ensure cash receipts are properly recorded and applied.'],
                            ['code' => 'AR-03', 'title' => 'AR Reconciliation and Subledger Integrity', 'description' => 'Ensure AR subledger reconciles to GL.'],
                            ['code' => 'AR-04', 'title' => 'Bad Debt Reserve and Write-off Control', 'description' => 'Ensure proper valuation of AR and authorization of write-offs.'],
                            ['code' => 'AR-05', 'title' => 'Order Processing and Billing Control', 'description' => 'Ensure orders are processed accurately and billed timely.'],
                        ]
                    ],
                ]
            ],
            [
                'code' => 'EXP',
                'title' => 'Procurement & Payables Cycle',
                'description' => 'Controls over Purchase-to-Pay process, including vendor management, procurement, and payment authorization.',
                'elements' => [
                    [
                        'title' => 'Procurement Authorization',
                        'description' => 'Verification of valid purchase requests and vendor management.',
                        'requirements' => [
                            ['code' => 'EXP-01', 'title' => 'Purchase Requisition and Approval', 'description' => 'Ensure procurement requests are properly authorized.'],
                            ['code' => 'EXP-02', 'title' => 'Vendor Selection and Management', 'description' => 'Ensure vendors are properly vetted and approved.'],
                            ['code' => 'EXP-03', 'title' => 'Purchase Order Authorization', 'description' => 'Ensure POs are properly authorized and issued.'],
                            ['code' => 'EXP-08', 'title' => 'Vendor Master File Changes', 'description' => 'Ensure vendor master file changes are authorized and verified.'],
                        ]
                    ],
                    [
                        'title' => 'Accounts Payable',
                        'description' => 'Processing of invoices, payments, and accruals.',
                        'requirements' => [
                            ['code' => 'EXP-04', 'title' => 'Three-Way Match Control', 'description' => 'Ensure PO, receipt, and invoice match before payment.'],
                            ['code' => 'EXP-05', 'title' => 'Invoice Processing and Coding', 'description' => 'Ensure invoices are processed accurately and coded correctly.'],
                            ['code' => 'EXP-06', 'title' => 'Payment Authorization and Processing', 'description' => 'Ensure payments are properly authorized and processed.'],
                            ['code' => 'EXP-07', 'title' => 'Expense Accruals', 'description' => 'Ensure expenses are properly accrued at period end.'],
                        ]
                    ],
                ]
            ],
            [
                'code' => 'ITGC',
                'title' => 'IT General Controls',
                'description' => 'Controls over financial system access, change management, computer operations, and data security.',
                'elements' => [
                    [
                        'title' => 'Access Management',
                        'description' => 'User identity and access control for financial systems.',
                        'requirements' => [
                            ['code' => 'ITGC-01', 'title' => 'User Access Provisioning and Removal', 'description' => 'Ensure user access is properly granted and revoked.'],
                            ['code' => 'ITGC-02', 'title' => 'Privileged Access Management', 'description' => 'Ensure privileged access is restricted and monitored.'],
                            ['code' => 'ITGC-03', 'title' => 'Quarterly User Access Review', 'description' => 'Ensure periodic review of user access rights.'],
                            ['code' => 'ITGC-04', 'title' => 'Segregation of Duties Enforcement', 'description' => 'Ensure SOD conflicts are identified and mitigated.'],
                        ]
                    ],
                    [
                        'title' => 'Change Management',
                        'description' => 'Software development and environment changes.',
                        'requirements' => [
                            ['code' => 'ITGC-05', 'title' => 'Program Change Management', 'description' => 'Ensure changes to production systems are authorized and tested.'],
                            ['code' => 'ITGC-06', 'title' => 'Emergency Change Control', 'description' => 'Ensure emergency changes are properly authorized and documented.'],
                            ['code' => 'ITGC-07', 'title' => 'Developer Access to Production', 'description' => 'Ensure developers cannot access production without approval.'],
                        ]
                    ],
                    [
                        'title' => 'Computer Operations',
                        'description' => 'IT operations, backup, and batch processing controls.',
                        'requirements' => [
                            ['code' => 'ITGC-08', 'title' => 'Backup and Recovery Control', 'description' => 'Ensure data backups are performed and tested.'],
                            ['code' => 'ITGC-09', 'title' => 'Job Scheduling and Monitoring', 'description' => 'Ensure batch jobs are scheduled and monitored.'],
                            ['code' => 'ITGC-10', 'title' => 'Problem and Incident Management', 'description' => 'Ensure IT issues are tracked and resolved.'],
                        ]
                    ],
                ]
            ],
        ],
    ];

    /**
     * Get category from requirement code
     */
    public static function getCategoryFromCode(string $code): string
    {
        if (str_starts_with($code, 'RC')) return 'Revenue';
        if (str_starts_with($code, 'AR')) return 'Receivables';
        if (str_starts_with($code, 'EXP')) return 'Procurement';
        if (str_starts_with($code, 'ITGC')) return 'ITGC';
        return 'General';
    }

    /**
     * Get COSO principle by category
     */
    public static function getCosoPrinciple(string $category): string
    {
        return match($category) {
            'Revenue' => 'P10 - Selects and develops control activities',
            'Receivables' => 'P12 - Deploys through policies and procedures',
            'Procurement' => 'P11 - Selects and develops general controls',
            'ITGC' => 'P11 - Selects and develops general controls over technology',
            default => 'P10 - General control activities',
        };
    }

    /**
     * Get control frequency by code
     */
    public static function getControlFrequency(string $code): string
    {
        return match($code) {
            'RC-01', 'RC-02', 'EXP-04', 'EXP-06', 'ITGC-03' => 'Monthly / Quarterly',
            'RC-03', 'AR-04', 'EXP-01', 'EXP-02', 'EXP-03' => 'Daily / Transactional',
            'AR-01', 'AR-02', 'AR-03', 'AR-05', 'EXP-05', 'EXP-07', 'EXP-08' => 'Daily',
            'ITGC-01', 'ITGC-02', 'ITGC-04', 'ITGC-05', 'ITGC-06', 'ITGC-07' => 'As needed / Quarterly',
            'ITGC-08', 'ITGC-09', 'ITGC-10' => 'Daily / Weekly',
            default => 'Monthly',
        };
    }

    /**
     * Get control type by code
     */
    public static function getControlType(string $code): string
    {
        return match($code) {
            'RC-01', 'RC-02', 'RC-03', 'EXP-04', 'EXP-06', 'AR-02', 'AR-03', 'ITGC-09' => 'Automated',
            'EXP-01', 'EXP-02', 'EXP-03', 'EXP-05', 'EXP-07', 'EXP-08' => 'Manual',
            'RC-04', 'AR-01', 'AR-04', 'AR-05' => 'Semi-automated',
            'ITGC-01', 'ITGC-02', 'ITGC-03', 'ITGC-04', 'ITGC-05', 'ITGC-06', 'ITGC-07', 'ITGC-08', 'ITGC-10' => 'Automated',
            default => 'Manual',
        };
    }

    /**
     * Get importance level by code
     */
    public static function getImportanceLevel(string $code): string
    {
        if (str_starts_with($code, 'ITGC')) {
            return 'IT General Control';
        }
        return in_array($code, ['RC-01', 'EXP-04', 'EXP-06', 'AR-02', 'ITGC-08']) 
            ? 'Key Control' 
            : 'Standard Control';
    }
}
