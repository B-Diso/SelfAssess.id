<?php

namespace Database\Seeders\ICoFR;

/**
 * Receivables Cycle Evidence Plans
 * Detailed SOX-compliant evidence plans for Accounts Receivable controls
 */
class ReceivablesEvidencePlans
{
    public static function get(string $code): string
    {
        return match($code) {
            'AR-01' => self::ar01Evidence(),
            'AR-02' => self::ar02Evidence(),
            'AR-03' => self::ar03Evidence(),
            'AR-04' => self::ar04Evidence(),
            'AR-05' => self::ar05Evidence(),
            default => self::defaultEvidence($code),
        };
    }

    private static function ar01Evidence(): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: AR-01 Credit Approval and Management</h3>
<p><strong>COSO Principle:</strong> Control Activities (P12) | <strong>SOX Relevance:</strong> High - Prevents bad debt</p>

<h4>📋 Sampling Methodology</h4>
<ul>
<li><strong>Sample Size:</strong> 25 new credit approvals</li>
<li><strong>Include:</strong> All customers with credit limit >$100,000</li>
<li><strong>Selection:</strong> Random across all business units</li>
</ul>

<h4>📄 Required Documents</h4>
<ul>
<li>Credit applications with financial statements</li>
<li>Dun & Bradstreet or other credit reports</li>
<li>Credit approval forms with authorization</li>
<li>Credit limit change logs</li>
</ul>

<h4>🧪 Test Procedures</h4>
<ol>
<li>Verify credit applications completed for all new customers</li>
<li>Check credit reports obtained for customers >$50,000</li>
<li>Verify approval hierarchy: Credit Manager, Controller, CFO</li>
<li>Check credit limits reviewed annually</li>
<li>Verify high-risk customers flagged appropriately</li>
</ol>

<h4>✅ Attributes to Test</h4>
<ul>
<li><strong>Authorization:</strong> Proper credit approval levels</li>
<li><strong>Completeness:</strong> Credit files maintained</li>
<li><strong>Timeliness:</strong> Annual credit reviews performed</li>
</ul>
HTML;
    }

    private static function ar02Evidence(): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: AR-02 Cash Receipts and Application Control</h3>
<p><strong>COSO Principle:</strong> Control Activities (P12) | <strong>SOX Relevance:</strong> Critical - Cash is high risk</p>

<h4>📋 Sampling Methodology</h4>
<ul>
<li><strong>Sample Size:</strong> 30 cash receipts</li>
<li><strong>Stratification:</strong> By value and method (wire, check, ACH)</li>
<li><strong>Include:</strong> All receipts >$100,000</li>
</ul>

<h4>📄 Required Documents</h4>
<ul>
<li>Bank deposit slips and lockbox reports</li>
<li>Cash application reports from ERP</li>
<li>Daily deposit reconciliation worksheets</li>
<li>Exception reports for unapplied cash</li>
</ul>

<h4>🧪 Test Procedures</h4>
<ol>
<li>Trace bank deposits to cash receipts journal</li>
<li>Verify cash applied to correct customer accounts</li>
<li>Check daily deposit reconciliation performed</li>
<li>Review exception items resolved timely</li>
<li>Verify segregation: Cash receipt vs cash application</li>
</ol>

<h4>✅ Attributes to Test</h4>
<ul>
<li><strong>Completeness:</strong> All cash receipts recorded</li>
<li><strong>Accuracy:</strong> Correct customer application</li>
<li><strong>Timeliness:</strong> Daily processing</li>
</ul>

<p><em><strong>⚠️ Critical:</strong> Cash receipt controls are high fraud risk. Verify dual controls.</em></p>
HTML;
    }

    private static function ar03Evidence(): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: AR-03 AR Reconciliation and Subledger Integrity</h3>
<p><strong>COSO Principle:</strong> Control Activities (P12) | <strong>SOX Relevance:</strong> High - Data integrity</p>

<h4>📋 Sampling Methodology</h4>
<ul>
<li><strong>Sample Size:</strong> All monthly reconciliations in period</li>
<li><strong>Focus:</strong> Quarter-end reconciliations</li>
</ul>

<h4>📄 Required Documents</h4>
<ul>
<li>AR subledger to GL reconciliation reports</li>
<li>Variance investigation documentation</li>
<li>Accounting Manager review sign-off</li>
<li>System interface error logs</li>
</ul>

<h4>🧪 Test Procedures</h4>
<ol>
<li>Verify daily automated reconciliation runs</li>
<li>Review monthly detailed reconciliation prepared</li>
<li>Check all variances investigated and documented</li>
<li>Verify Accounting Manager sign-off on reconciliations</li>
<li>Test system interface between AR and GL</li>
</ol>

<h4>✅ Attributes to Test</h4>
<ul>
<li><strong>Completeness:</strong> All reconciliations performed</li>
<li><strong>Accuracy:</strong> Subledger matches GL</li>
<li><strong>Timeliness:</strong> Daily/monthly as defined</li>
</ul>
HTML;
    }

    private static function ar04Evidence(): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: AR-04 Bad Debt Reserve and Write-off Control</h3>
<p><strong>COSO Principle:</strong> Control Activities (P12) | <strong>SOX Relevance:</strong> High - Valuation assertion</p>

<h4>📋 Sampling Methodology</h4>
<ul>
<li><strong>Sample Size:</strong> All write-offs >$10,000 + 20 random smaller</li>
<li><strong>Include:</strong> Quarterly reserve calculations</li>
</ul>

<h4>📄 Required Documents</h4>
<ul>
<li>AR aging reports by period</li>
<li>Bad debt reserve calculation worksheets</li>
<li>Write-off approval forms</li>
<li>Collection effort documentation</li>
</ul>

<h4>🧪 Test Procedures</h4>
<ol>
<li>Recalculate reserve based on aging and historical rates</li>
<li>Verify write-offs >$10,000 have CFO approval</li>
<li>Check collection efforts documented before write-off</li>
<li>Review specific reserves for large doubtful accounts</li>
<li>Verify reserve methodology consistent period-to-period</li>
</ol>

<h4>✅ Attributes to Test</h4>
<ul>
<li><strong>Valuation:</strong> Reserve is adequate</li>
<li><strong>Authorization:</strong> Proper write-off approvals</li>
<li><strong>Consistency:</strong> Methodology unchanged</li>
</ul>
HTML;
    }

    private static function ar05Evidence(): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: AR-05 Order Processing and Billing Control</h3>
<p><strong>COSO Principle:</strong> Control Activities (P12) | <strong>SOX Relevance:</strong> Medium - Accuracy</p>

<h4>📋 Sampling Methodology</h4>
<ul>
<li><strong>Sample Size:</strong> 30 orders</li>
<li><strong>Stratification:</strong> By order value and complexity</li>
</ul>

<h4>📄 Required Documents</h4>
<ul>
<li>Customer purchase orders</li>
<li>Sales orders with approval stamps</li>
<li>Pick tickets and packing slips</li>
<li>Invoices generated by system</li>
</ul>

<h4>🧪 Test Procedures</h4>
<ol>
<li>Verify customer master validated during order entry</li>
<li>Check pick tickets match packing slips</li>
<li>Verify invoices generated within 24 hours of shipment</li>
<li>Test invoice accuracy: customer, product, quantity, price</li>
<li>Verify credit hold flags enforced</li>
</ol>

<h4>✅ Attributes to Test</h4>
<ul>
<li><strong>Accuracy:</strong> Correct billing details</li>
<li><strong>Timeliness:</strong> Invoicing within SLA</li>
<li><strong>Completeness:</strong> All shipments billed</li>
</ul>
HTML;
    }

    private static function defaultEvidence(string $code): string
    {
        return <<<HTML
<h3>🔍 Evidence Plan: {$code}</h3>
<p>Please refer to standard ICoFR testing procedures.</p>
HTML;
    }
}
