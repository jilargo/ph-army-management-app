<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Philippine Army – Personnel Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1f2937;
            line-height: 1.5;
        }

        /* ===== Header ===== */
        .header-band {
            background-color: #173a2f;
            color: #ffffff;
            padding: 22px 30px;
            border-bottom: 5px solid #c9a227;
        }

        .header-band .republic {
            font-size: 10px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #d7e3d9;
        }

        .header-band .title {
            font-size: 26px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-top: 4px;
        }

        .header-band .subtitle {
            font-size: 11px;
            color: #cfe0d3;
            margin-top: 2px;
        }

        .header-band .report-title {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 4px;
            text-transform: uppercase;
            margin-top: 10px;
            color: #c9a227;
        }

        .meta {
            padding: 10px 30px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
            color: #6b7280;
        }

        .meta table {
            width: 100%;
        }

        .meta td.right {
            text-align: right;
        }

        /* ===== Sections ===== */
        .section {
            padding: 18px 30px 6px;
        }

        h2.section-heading {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #173a2f;
            border-bottom: 2px solid #173a2f;
            padding-bottom: 5px;
            margin-bottom: 12px;
        }

        /* ===== Stat tiles ===== */
        table.tiles {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        table.tiles td {
            width: 25%;
        }

        .tile {
            border: 1px solid #e0e4e0;
            border-left: 4px solid #173a2f;
            padding: 12px 12px 10px;
            margin: 0 6px 8px 0;
            border-radius: 4px;
        }

        .tile .label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6b7280;
        }

        .tile .value {
            font-size: 22px;
            font-weight: bold;
            color: #173a2f;
            margin-top: 3px;
        }

        .tile .hint {
            font-size: 9px;
            color: #9ca3af;
            margin-top: 2px;
        }

        .tile.green { border-left-color: #16a34a; }
        .tile.red { border-left-color: #dc2626; }
        .tile.blue { border-left-color: #2563eb; }
        .tile.amber { border-left-color: #d97706; }
        .tile.slate { border-left-color: #64748b; }

        /* ===== General tables ===== */
        table.grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        table.grid th {
            background-color: #173a2f;
            color: #ffffff;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-align: left;
            padding: 7px 10px;
        }

        table.grid td {
            padding: 7px 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        table.grid tr.striped td {
            background-color: #f4f7f4;
        }

        .row-table {
            width: 100%;
            border-collapse: collapse;
        }

        .row-table td {
            vertical-align: top;
        }

        .row-table td.left-col {
            width: 55%;
        }

        .row-table td.right-col {
            width: 45%;
        }

        .dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
        }

        .dot.green { background-color: #16a34a; }
        .dot.red { background-color: #dc2626; }
        .dot.blue { background-color: #2563eb; }
        .dot.amber { background-color: #d97706; }
        .dot.slate { background-color: #64748b; }

        .badge {
            display: inline-block;
            font-size: 9px;
            font-weight: bold;
            padding: 2px 9px;
            border-radius: 10px;
            color: #ffffff;
        }

        .badge.active { background-color: #16a34a; }
        .badge.inactive { background-color: #dc2626; }
        .badge.retired { background-color: #64748b; }
        .badge.leave { background-color: #2563eb; }
        .badge.completed { background-color: #16a34a; }
        .badge.in_progress { background-color: #d97706; }
        .badge.pending { background-color: #d97706; }
        .badge.accepted { background-color: #16a34a; }
        .badge.rejected { background-color: #dc2626; }

        .empty {
            text-align: center;
            color: #9ca3af;
            padding: 18px;
        }

        .footer {
            margin-top: 10px;
            padding: 0 30px 20px;
            font-size: 9px;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>
<body>

    {{-- ===== Header ===== --}}
    <div class="header-band">
        <div class="republic">Republic of the Philippines</div>
        <div class="title">PHILIPPINE ARMY</div>
        <div class="subtitle">Department of National Defense &nbsp;&bull;&nbsp; General Headquarters</div>
        <div class="report-title">Personnel Strength Report</div>
    </div>

    <div class="meta">
        <table>
            <tr>
                <td>
                    Report Date: <strong>{{ $generatedAt->format('F j, Y') }}</strong> &nbsp;|&nbsp;
                    Generated By: <strong>{{ $generatedBy }}</strong>
                </td>
                <td class="right">
                    Prepared for presentation &amp; internal reporting
                </td>
            </tr>
        </table>
    </div>

    {{-- ===== Headline statistics ===== --}}
    <div class="section">
        <h2 class="section-heading">Command Overview</h2>
        <table class="tiles">
            <tr>
                <td>
                    <div class="tile"><div class="label">Total Personnel</div>
                        <div class="value">{{ number_format($totalPersonnel) }}</div>
                        <div class="hint">All service members</div></div>
                </td>
                <td>
                    <div class="tile green"><div class="label">Active Soldiers</div>
                        <div class="value">{{ number_format($activeSoldiers) }}</div>
                        <div class="hint">Currently serving</div></div>
                </td>
                <td>
                    <div class="tile blue"><div class="label">Newly Accepted</div>
                        <div class="value">{{ number_format($newlyAccepted) }}</div>
                        <div class="hint">Last 30 days</div></div>
                </td>
                <td>
                    <div class="tile"><div class="label">Active Units</div>
                        <div class="value">{{ number_format($activeUnits) }}</div>
                        <div class="hint">Registered units</div></div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="tile"><div class="label">Pending Leaves</div>
                        <div class="value">{{ number_format($pendingLeaves) }}</div>
                        <div class="hint">Awaiting review</div></div>
                </td>
                <td>
                    <div class="tile"><div class="label">Pending Promotions</div>
                        <div class="value">{{ number_format($pendingPromotions) }}</div>
                        <div class="hint">Awaiting approval</div></div>
                </td>
                <td>
                    <div class="tile amber"><div class="label">Open Tasks</div>
                        <div class="value">{{ number_format($openTasks) }}</div>
                        <div class="hint">In progress &amp; pending</div></div>
                </td>
                <td>
                    <div class="tile green"><div class="label">Completed Tasks</div>
                        <div class="value">{{ number_format($completedTasks) }}</div>
                        <div class="hint">Accomplishments</div></div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ===== Breakdowns ===== --}}
    <div class="section">
        <h2 class="section-heading">Personnel Breakdown</h2>
        <table class="row-table">
            <tr>
                <td class="left-col">
                    <table class="grid">
                        <tr>
                            <th>Status</th>
                            <th style="width:80px">Count</th>
                        </tr>
                        <tr><td><span class="dot green"></span>Active</td><td>{{ number_format($activeSoldiers) }}</td></tr>
                        <tr class="striped"><td><span class="dot red"></span>Inactive</td><td>{{ number_format($inactiveSoldiers) }}</td></tr>
                        <tr><td><span class="dot blue"></span>On Leave</td><td>{{ number_format($onLeaveSoldiers) }}</td></tr>
                        <tr class="striped"><td><span class="dot slate"></span>Retired</td><td>{{ number_format($retiredSoldiers) }}</td></tr>
                        <tr><td>Newly accepted (30 days)</td><td>{{ number_format($newlyAccepted) }}</td></tr>
                    </table>
                </td>
                <td class="right-col">
                    <table class="grid">
                        <tr>
                            <th>Rank</th>
                            <th style="width:80px">Count</th>
                        </tr>
                        @forelse ($rankDistribution as $row)
                            <tr>
                                <td>{{ $row['rank'] }}</td>
                                <td>{{ number_format($row['total']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="empty">No personnel records.</td></tr>
                        @endforelse
                    </table>
                </td>
            </tr>
        </table>
    </div>

    {{-- ===== Newly Accepted Soldiers ===== --}}
    <div class="section">
        <h2 class="section-heading">Newly Accepted Soldiers</h2>
        <table class="grid">
            <thead>
                <tr>
                    <th style="width:28px">#</th>
                    <th>Name</th>
                    <th>Rank</th>
                    <th>Unit</th>
                    <th>Entry Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($newlyAcceptedList as $index => $newcomer)
                    <tr @class(['striped' => $index % 2 === 1])>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $newcomer->full_name }}</td>
                        <td>{{ $newcomer->rank?->rank_name ?? '—' }}</td>
                        <td>{{ $newcomer->units?->unit_name ?? '—' }}</td>
                        <td>{{ $newcomer->date_of_entry ? \Carbon\Carbon::parse($newcomer->date_of_entry)->format('M d, Y') : '—' }}</td>
                        <td>
                            <span class="badge {{ strtolower($newcomer->status) }}">
                                {{ strtolower($newcomer->status) === 'leave' ? 'On Leave' : $newcomer->status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">No soldiers accepted in the last 30 days.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ===== Tasks & Accomplishments ===== --}}
    <div class="section">
        <h2 class="section-heading">Tasks &amp; Accomplishments</h2>
        <table class="grid">
            <thead>
                <tr>
                    <th>Task</th>
                    <th>Type</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Assignee</th>
                    <th>Due Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tasks as $index => $task)
                    <tr @class(['striped' => $index % 2 === 1])>
                        <td>
                            <strong>{{ $task->title }}</strong>
                            @if ($task->description)
                                <br><span style="color:#6b7280;font-size:9px">{{ $task->description }}</span>
                            @endif
                        </td>
                        <td>{{ ucfirst($task->type ?? '—') }}</td>
                        <td>{{ ucfirst($task->priority) }}</td>
                        <td>
                            <span class="badge {{ $task->status }}">
                                {{ str_replace('_', ' ', ucfirst($task->status)) }}
                            </span>
                        </td>
                        <td>{{ $task->personnel?->full_name ?? '—' }}</td>
                        <td>{{ $task->due_date ? $task->due_date->format('M d, Y') : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">No tasks assigned.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ===== Enlistment Applications ===== --}}
    <div class="section">
        <h2 class="section-heading">Enlistment Applications</h2>

        <table class="row-table" style="margin-bottom:10px">
            <tr>
                <td class="left-col">
                    <table class="grid">
                        <tr>
                            <th>Status</th>
                            <th style="width:80px">Count</th>
                        </tr>
                        <tr><td><span class="dot amber"></span>Pending</td><td>{{ number_format($pendingApplications) }}</td></tr>
                        <tr class="striped"><td><span class="dot green"></span>Accepted / Enlisted</td><td>{{ number_format($acceptedApplications) }}</td></tr>
                        <tr><td><span class="dot red"></span>Rejected</td><td>{{ number_format($rejectedApplications) }}</td></tr>
                    </table>
                </td>
                <td class="right-col"></td>
            </tr>
        </table>

        <table class="grid">
            <thead>
                <tr>
                    <th style="width:28px">#</th>
                    <th>Applicant</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th>Documents</th>
                    <th>Submitted</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($enlistments as $index => $application)
                    <tr @class(['striped' => $index % 2 === 1])>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $application->full_name }}</td>
                        <td>{{ $application->contact_number }}</td>
                        <td>
                            <span class="badge {{ $application->status }}">
                                {{ ucfirst($application->status) }}
                            </span>
                        </td>
                        <td>{{ $application->documents_count }}</td>
                        <td>{{ $application->created_at?->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">No enlistment applications on record.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ===== Roster ===== --}}
    <div class="section">
        <h2 class="section-heading">Personnel Roster</h2>
        <table class="grid">
            <thead>
                <tr>
                    <th style="width:28px">#</th>
                    <th>Name</th>
                    <th>Rank</th>
                    <th>Unit</th>
                    <th>Status</th>
                    <th>Entry Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($personnel as $index => $personnel_row)
                    <tr @class(['striped' => $index % 2 === 1])>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $personnel_row->full_name }}</td>
                        <td>{{ $personnel_row->rank?->rank_name ?? '—' }}</td>
                        <td>{{ $personnel_row->units?->unit_name ?? '—' }}</td>
                        <td>
                            <span class="badge {{ strtolower($personnel_row->status) }}">
                                {{ strtolower($personnel_row->status) === 'leave' ? 'On Leave' : $personnel_row->status }}
                            </span>
                        </td>
                        <td>{{ $personnel_row->date_of_entry ? \Carbon\Carbon::parse($personnel_row->date_of_entry)->format('M d, Y') : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">No personnel records available.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        This report is generated by the Philippine Army Management System and is intended for internal presentation use only.
    </div>

</body>
</html>