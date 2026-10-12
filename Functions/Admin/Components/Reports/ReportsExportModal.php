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
                <label>Select Export File Format</label>
                <div class="ra-format-cards">
                    <div class="ra-format-option active" data-format="PDF">
                        <span class="material-symbols-outlined" style="color: #dc2626;">picture_as_pdf</span>
                        <span>PDF Document</span>
                    </div>
                    <div class="ra-format-option" data-format="XLSX">
                        <span class="material-symbols-outlined" style="color: #16a34a;">table_view</span>
                        <span>Excel (.xlsx)</span>
                    </div>
                    <div class="ra-format-option" data-format="CSV">
                        <span class="material-symbols-outlined" style="color: #2563eb;">description</span>
                        <span>CSV Sheet</span>
                    </div>
                </div>
            </div>

            <div class="ra-form-group">
                <label for="raReportTimeframe">Time Horizon</label>
                <select id="raReportTimeframe" class="ra-form-select">
                    <option value="30d">Current Month (September 2026)</option>
                    <option value="quarter">Third Quarter (Q3 2026)</option>
                    <option value="ytd">Year to Date (Jan - Sep 2026)</option>
                    <option value="lifetime">Full Lifetime Historical Data</option>
                </select>
            </div>

            <div style="background: #f8fafc; border: 1px solid var(--ra-border); border-radius: 10px; padding: 12px 14px; display: flex; flex-direction: column; gap: 8px;">
                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #334155; cursor: pointer;">
                    <input type="checkbox" checked style="accent-color: #2563eb; width: 16px; height: 16px;">
                    Include high-resolution graphical charts and visual summaries
                </label>
                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #334155; cursor: pointer;">
                    <input type="checkbox" checked style="accent-color: #2563eb; width: 16px; height: 16px;">
                    Anonymize student private contact details in export
                </label>
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
