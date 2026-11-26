<!--  Page Header (Title, Subtitle, Period Pills & Export Actions)-->
<div class="ra-header-row">
    <div class="ra-header-info">
        <h1>Reports & Analytics</h1>
        <p>Summary of student placements, partner universities, companies, and platform activity.</p>
    </div>

    <div class="ra-header-actions">
        <!-- Time Period Selector Pills -->
        <div class="ra-period-pills">
            <button type="button" class="ra-period-btn active" data-period="30d">Last 30 Days</button>
            <button type="button" class="ra-period-btn" data-period="quarter">This Quarter</button>
            <button type="button" class="ra-period-btn" data-period="ytd">Year to Date</button>
            <button type="button" class="ra-period-btn" data-period="all">All Time</button>
        </div>

        <!-- Export Action -->
        <button type="button" class="ra-btn-export" id="raBtnOpenExport">
            <span class="material-symbols-outlined">download</span>
            Export CSV
        </button>

        <!-- Print Action -->
        <button type="button" class="ra-btn-secondary" id="raBtnPrint" title="Print Summary">
            <span class="material-symbols-outlined">print</span>
            Print
        </button>
    </div>
</div>
