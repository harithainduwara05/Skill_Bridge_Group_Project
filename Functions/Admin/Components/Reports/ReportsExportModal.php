<!--  Export Report Custom Modal (Mockup with format & scope options)-->
<div class="ra-modal-backdrop" id="raExportModal">
    <div class="ra-modal-dialog">
        <div class="ra-modal-header">
            <h3>Generate Analytics Report</h3>
            <button type="button" class="ra-modal-close" id="raBtnCloseExport" title="Close">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <div class="ra-modal-body">
            
            <div class="ra-form-group">
                <label for="raReportScope">Select Report Scope</label>
                <select id="raReportScope" class="ra-form-select">
                    <option value="executive">Full Platform Executive Summary (All Domains)</option>
                    <option value="placements">Student Placements & Hiring Velocity Report</option>
                    <option value="universities">Partner Universities Audit & Completion Index</option>
                    <option value="companies">Corporate Recruiters & Openings Audit</option>
                    <option value="complaints">Grievances Resolution & Support Log</option>
                </select>
            </div>

            <div class="ra-form-group">
                <label>Export File Format</label>
                <div class="ra-format-cards">
                    <div class="ra-format-option active" data-format="CSV">
                        <span class="material-symbols-outlined" style="color: #2563eb;">description</span>
                        <span>CSV Sheet</span>
                    </div>
                </div>
            </div>

            <div class="ra-form-group">
                <label for="raReportTimeframe">Time Horizon</label>
                <select id="raReportTimeframe" class="ra-form-select">
                    <option value="30d">Last 30 Days (<?= date('F Y') ?>)</option>
                    <option value="quarter">Last 90 Days (Quarter)</option>
                    <option value="ytd">Year to Date (<?= date('Y') ?>)</option>
                    <option value="lifetime">All Time Historical Data</option>
                </select>
            </div>

        </div>

        <div class="ra-modal-footer">
            <button type="button" class="ra-btn-secondary" id="raBtnCancelExport">Cancel</button>
            <button type="button" class="ra-btn-export" id="raBtnGenerateReport">
                <span class="material-symbols-outlined">download</span>
                Generate & Download
            </button>
        </div>
    </div>
</div>
